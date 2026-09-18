<?php
namespace skeeks\cms\shop\gpd;

use skeeks\cms\models\CmsContentProperty;
use skeeks\cms\models\CmsContentPropertyEnum;
use skeeks\cms\models\CmsStorageFile;
use skeeks\cms\models\CmsTree;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopProduct;
use skeeks\cms\shop\models\ShopBrand;
use skeeks\cms\shop\models\ShopCollection;
use skeeks\cms\shop\models\ShopProductModel;
use skeeks\cms\shop\components\GpdComponent;
use yii\db\Query;

/** New catalog writer. Does not call or instantiate legacy console controllers. */
final class ShopCatalogWriter implements CatalogWriterInterface
{
    private $site; private $settings; private $api; private $images=[]; private $stableReferences; private $checkpoint;
    public function __construct(int $site,GpdComponent $settings,$api,callable $checkpoint)
    {$this->site=$site;$this->settings=$settings;$this->api=$api;$this->checkpoint=$checkpoint;}
    private function query(int $id)
    {return ShopCmsContentElement::find()->andWhere(['sx_id'=>$id,'cms_site_id'=>$this->site]);}
    private function model(int $id)
    {
        $rows=$this->query($id)->limit(2)->all();
        if(count($rows)>1)throw new \RuntimeException('Найдены дубли товара GPD '.$id.'.');
        return $rows[0]??null;
    }
    private function save($model): void
    {
        if(!$model->save())throw new \RuntimeException('Не удалось сохранить '.(new \ReflectionClass($model))->getShortName().': '.json_encode($model->errors,JSON_UNESCAPED_UNICODE));
    }
    public function prepare(array $item): void
    {
        if($item['operation']!=='upsert')return;
        $model=$this->model($item['id']);
        if($model && (!$this->settings->updateProducts || !$model->is_sx_info_update))return;
        if(!$model && !$this->settings->createProducts)return;
        $d=$item['data'];
        $this->prepareStableReferences($d);
        $this->reference('categories',(int)($d['category_id']??0));
        $this->reference('brands',(int)($d['brand_id']??0));
        foreach($d['collection_ids']??[] as $id)$this->reference('collections',(int)$id);
        foreach($d['properties']??[] as $p) {
            foreach (isset($p['value']['id']) ? [$p['value']] : (is_array($p['value'] ?? null) ? $p['value'] : []) as $enum) {
                if (is_array($enum) && isset($enum['property_id']) && (int)$enum['property_id'] !== (int)$p['property_id']) {
                    throw new \RuntimeException('Некорректные данные GPD: значение #'.($enum['id']??0).' принадлежит характеристике #'.$enum['property_id'].', а указана #'.$p['property_id'].'.');
                }
            }
            $this->reference('properties',(int)$p['property_id']);
            $v=$p['value']??null;
            if(is_array($v)) {
                foreach(isset($v['id'])?[$v]:$v as $e)if(is_array($e)&&isset($e['image']))$this->image($e['image']);
            }
        }
        $this->image($d['image']??null);
        foreach($d['images']??[] as $img)$this->image($img);
    }
    /** All new catalog jobs use v2; an absent dependency is never fetched from v1. */
    private function reference(string $kind,int $id)
    {
        if(!$id)return null;
        if(!\Yii::$app->gpdReceiver->referencesEnabled)throw new ReferencePendingException('Включите синхронизацию справочников GPD v2.');
        return ShopReferenceWriter::required($this->site,\Yii::$app->gpdReceiver->connectionIdForSite($this->site),$kind,$id);
    }
    public function prepareStableReferences(array $data): void
    {
        if(empty($data['country_alpha2'])&&empty($data['measure_code'])&&empty($data['measure_matches']))return;
        if(!$this->stableReferences)$this->stableReferences=StableReferenceSync::forSite($this->site,$this,$this->checkpoint);
        $this->stableReferences->ensure('countries',(string)($data['country_alpha2']??''));
        $this->stableReferences->ensure('measures',(string)($data['measure_code']??''));
        foreach(array_keys($data['measure_matches']??[]) as $code)$this->stableReferences->ensure('measures',(string)$code);
    }
    public function image($data, bool $forceDownload=false): ?int
    {
        if(!$data)return null;
        $id=(int)($data['id']??0);$src=(string)($data['src']??'');
        if(!$id||$src==='')throw new ProtocolException('invalid_image');
        $mode=($forceDownload || !empty($this->api->is_download_images))?'download':'link';
        $key=$id.':'.$src.':'.$mode;
        if(isset($this->images[$key]) && CmsStorageFile::find()->where(['id'=>$this->images[$key]])->exists())return $this->images[$key];
        ($this->checkpoint)();
        $external='gpd:'.$mode.':'.$id.':'.substr(hash('sha256',$src),0,20);
        $file=CmsStorageFile::find()->andWhere(['external_id'=>$external])->one();
        if($file)return $this->images[$key]=(int)$file->id;
        $source=CmsStorageFile::find()->andWhere(['sx_id'=>$id])->one();
        $file=$source;
        // Match v1: dictionaries may force download; other images follow the API component.
        if($file && (($mode==='link' && $file->cluster_id==='sx' && $file->cluster_file===$src) || ($mode==='download' && $file->cluster_id!=='sx')))return $this->images[$key]=(int)$file->id;
        if($mode==='download') {
            try {$file=\Yii::$app->storage->upload($this->api->getImageUrl($src));}
            catch(\Throwable $e){throw new \RuntimeException('Не удалось скачать изображение GPD #'.$id.'.');}
        } else {
        $file=new CmsStorageFile();$file->cluster_id='sx';$file->cluster_file=$src;
        $file->original_name=pathinfo($src,PATHINFO_FILENAME);$file->extension=pathinfo(parse_url($src,PHP_URL_PATH),PATHINFO_EXTENSION)?:'webp';
        $file->mime_type=['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','webp'=>'image/webp'][$file->extension]??'image/webp';
        $file->image_width=(int)($data['width']??0);$file->image_height=(int)($data['height']??0);
        }
        $file->sx_id=$source?null:$id;$file->external_id=$external;$file->sx_data=$data;$this->save($file);
        return $this->images[$key]=(int)$file->id;
    }
    public function apply(array $item,array $state): array
    {
        $model=$this->model($item['id']);
        if($item['operation']==='revoke')return $this->revoke($model,$state);
        if(!$model&&!$this->settings->createProducts)return ['outcome'=>'pending'];
        $created=!$model;
        if($created) {
            $model=new ShopCmsContentElement();$model->sx_id=$item['id'];$model->cms_site_id=$this->site;
            $model->content_id=\Yii::$app->shop->contentProducts->id;$model->is_active=(int)$this->settings->newProductsActive;
        }
        $reactivate=!$created && $state['deactivated_by_gpd'] && $this->settings->reactivateProducts;
        if(!$created && (!$this->settings->updateProducts||!$model->is_sx_info_update)) {
            if($reactivate){$model->is_active=1;$this->save($model);}
            return ['outcome'=>'protected','local_product_id'=>$model->id,'deactivated_by_gpd'=>$reactivate?0:(int)$state['deactivated_by_gpd']];
        }
        $d=$item['data'];
        foreach(['name','description_short','description_full','is_adult'] as $field)if(array_key_exists($field,$d))$model->$field=$d[$field];
        if(trim((string)$model->name)==='')throw new ProtocolException('empty_product_name');
        if(array_key_exists('category_id',$d))$model->tree_id=($this->reference('categories',(int)$d['category_id']))->id??null;
        if(array_key_exists('image',$d))$model->image_id=$this->image($d['image']);
        if($reactivate)$model->is_active=1;
        $this->save($model);
        $product=$model->shopProduct?:new ShopProduct(['id'=>$model->id]);
        foreach(['brand_sku','country_alpha2','measure_code','weight','width','length','height','measure_ratio','measure_ratio_min','expiration_time','service_life_time','warranty_time','expiration_time_comment','service_life_time_comment','warranty_time_comment'] as $field) {
                        if(array_key_exists($field,$d)) {
                $value=$d[$field];
                if(in_array($field,['weight','width','length','height','measure_ratio','measure_ratio_min'],true))$value=(float)$value;
                if(in_array($field,['expiration_time','service_life_time','warranty_time'],true))$value=(int)$value;
                if($field==='country_alpha2'&&!$value)$value=null;
                $product->$field=$value;
            }
        }
        if(array_key_exists('brand_id',$d))$product->brand_id=($this->reference('brands',(int)$d['brand_id']))->id??null;
        if(array_key_exists('measure_matches',$d))$product->measure_matches_jsondata=json_encode($d['measure_matches'],JSON_UNESCAPED_UNICODE);
        if(array_key_exists('collection_ids',$d)) {
            $ids=[];foreach($d['collection_ids'] as $id)$ids[]=$this->reference('collections',(int)$id)->id;
            $product->collections=$ids;
        }
        if(array_key_exists('model_id',$d)) {
            $group=null;
            if($d['model_id']){$group=ShopProductModel::find()->andWhere(['sx_id'=>$d['model_id']])->one();if(!$group){$group=new ShopProductModel(['sx_id'=>$d['model_id']]);$this->save($group);}}
            // Each card owns only its own membership; never detach other pending/protected cards.
            $product->shop_product_model_id=$group?$group->id:null;
        }
        foreach(['width','length','height','weight'] as $field)if($product->$field===null)$product->$field=0;
                // API cards may omit dimensions required by the site's manual editing form.
        // Validate transport numeric values, but do not invent missing dimensions.
        foreach(['weight','width','length','height','measure_ratio','measure_ratio_min'] as $field) {
            if(!is_numeric($product->$field)||(float)$product->$field<0)throw new ProtocolException('invalid_product_number');
        }
        if(!$product->save(false))throw new \RuntimeException('Не удалось сохранить параметры товара.');
        if(array_key_exists('images',$d)) {
            $ids=[];foreach($d['images'] as $image)$ids[]=$this->image($image);
            $model->setImageIds(array_values(array_unique($ids)));$this->save($model);
        }
        if(array_key_exists('properties',$d))$this->properties($model,$d['properties']);
        return ['outcome'=>$created?'created':'updated','local_product_id'=>$model->id,'deactivated_by_gpd'=>$reactivate?0:(int)$state['deactivated_by_gpd']];
    }
    private function properties($model,array $items): void
    {
        $values=[];
        foreach($items as $item) {
            $p=$this->reference('properties',(int)$item['property_id']);$value=$item['value']??null;
            if($p->property_type==='L') {
                $enumIds=[];
                $enums=$value===null||$value===[]?[]:(is_array($value)&&isset($value['id'])?[$value]:$value);
                if(!is_array($enums)||array_values($enums)!==$enums)throw new ProtocolException('invalid_property_enum');
                if(!$p->is_multiple&&count($enums)>1)throw new ReferencePendingException('Ожидается множественная характеристика #'.$item['property_id'].'.');
                foreach($enums as $v) {
                    if(!is_array($v)||empty($v['id']))throw new ProtocolException('invalid_property_enum');
                    if(\Yii::$app->gpdReceiver->referencesEnabled) {
                        $enum=CmsContentPropertyEnum::find()->andWhere(['property_id'=>$p->id,'sx_id'=>$v['id']])->one();
                        if(!$enum)throw new ReferencePendingException('Ожидается значение характеристики #'.$v['id'].'.');
                        $enumIds[]=$enum->id;continue;
                    }
                    $enum=CmsContentPropertyEnum::find()->andWhere(['property_id'=>$p->id,'sx_id'=>$v['id']])->one()?:new CmsContentPropertyEnum(['property_id'=>$p->id,'sx_id'=>$v['id']]);
                    $enum->value=(string)$v['value'];
                    if(array_key_exists('image',$v))$enum->cms_image_id=$this->image($v['image']);
                    $this->save($enum);$enumIds[]=$enum->id;
                }
                $value=$p->is_multiple?$enumIds:($enumIds[0]??null);
            }
            $values[$p->code]=$value;
        }
                $rpm=$model->relatedPropertiesModel;
        $sourceCodes=CmsContentProperty::find()->select('code')->andWhere(['cms_site_id'=>$this->site])->andWhere(['>','sx_id',0])->column();
        foreach(array_intersect($rpm->attributes(),$sourceCodes) as $code)if(!array_key_exists($code,$values))$values[$code]=null;
        $rpm->setAttributes($values,false);
        if(!$rpm->save())throw new \RuntimeException('Не удалось сохранить характеристики товара #'.$model->id.'.');
    }
    private function revoke($model,array $state): array
    {
        if(!$model)return ['outcome'=>'absent','local_product_id'=>null,'deactivated_by_gpd'=>0];
        if($this->settings->excludedAction==='keep')return ['outcome'=>'kept','local_product_id'=>$model->id];
        $other=(new Query())->from(['p'=>'{{%shop_store_product}}'])->innerJoin(['s'=>'{{%shop_store}}'],'s.id=p.shop_store_id')
            ->where(['p.shop_product_id'=>$model->id,'p.is_active'=>1])->andWhere(['or',['s.sx_id'=>null],['s.sx_id'=>0]])->exists();
        if($other)return ['outcome'=>'other_source','local_product_id'=>$model->id];
        if($this->settings->excludedAction==='delete') {
            $savepoint=\Yii::$app->db->beginTransaction();
            try {
                $positions=(new CatalogRemoval(\Yii::$app->db))->positions((int)$model->id,$this->site);
                if($positions!==null) {
                    foreach($positions as $id) {
                        $position=\skeeks\cms\shop\models\ShopStoreProduct::findOne($id);
                        if(!$position || $position->delete()===false)throw new \RuntimeException('Удаление складской позиции отменено обработчиком.');
                    }
                    if($model->delete()===false)throw new \RuntimeException('Удаление товара отменено обработчиком.');
                    $savepoint->commit();
                    return ['outcome'=>'deleted','local_product_id'=>null,'deactivated_by_gpd'=>0];
                }
                $savepoint->rollBack();
            } catch(\yii\db\IntegrityException $e) {
                $savepoint->rollBack();
                $model=$this->model((int)$model->sx_id);
            } catch(\Throwable $e) {$savepoint->rollBack();throw $e;}
        }
        $flag=(int)$state['deactivated_by_gpd'];
        if($model->is_active){$model->is_active=0;$this->save($model);$flag=1;}
        return ['outcome'=>'deactivated','local_product_id'=>$model->id,'deactivated_by_gpd'=>$flag];
    }
}
