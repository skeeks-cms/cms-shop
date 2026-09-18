<?php
namespace skeeks\cms\shop\gpd;

use skeeks\cms\models\CmsContentProperty;
use skeeks\cms\models\CmsContentPropertyEnum;
use skeeks\cms\models\CmsTree;
use skeeks\cms\shop\models\ShopBrand;
use skeeks\cms\shop\models\ShopCollection;
use yii\db\Query;

/** Dictionary writes and their acknowledged source version share the applier transaction. */
final class ShopReferenceWriter implements CatalogWriterInterface
{
    public const CLASSES=['categories'=>CmsTree::class,'brands'=>ShopBrand::class,'properties'=>CmsContentProperty::class,'collections'=>ShopCollection::class];
    private $site;private $connection;private $media;
    public function __construct(int $site,string $connection,ShopCatalogWriter $media)
    {$this->site=$site;$this->connection=$connection;$this->media=$media;}
    public static function find(int $site,string $kind,int $id)
    {
        if(!isset(self::CLASSES[$kind])||$id<1)throw new ProtocolException('invalid_reference');
        $class=self::CLASSES[$kind];$q=$class::find()->andWhere(['sx_id'=>$id]);
        if(in_array($kind,['categories','properties'],true))$q->andWhere(['cms_site_id'=>$site]);
        $models=$q->limit(2)->all();
        if(count($models)>1)throw new \RuntimeException('Дубли справочника '.$kind.' #'.$id.'.');
        return $models[0]??null;
    }
    public static function required(int $site,string $connection,string $kind,int $id)
    {
        if(!$id)return null;
        $state=(new Query())->from('{{%shop_gpd_reference_state}}')->where(['connection_id'=>$connection,'kind'=>$kind,'source_id'=>$id,'operation'=>'upsert','needs_resolution'=>0])
            ->andWhere('applied_revision >= revision')->andWhere(['>','applied_revision',0])->one();
        $model=$state?self::find($site,$kind,$id):null;
        if(!$model)throw new ReferencePendingException('Ожидается справочник '.$kind.' #'.$id.'.');
        return $model;
    }
    private function decode(array $item): array
    {
        $d=$item['data']??null;
        if(!is_array($d)||!isset(self::CLASSES[$d['kind']??''])||!is_int($d['source_id']??null)||$d['source_id']<1||!is_array($d['payload']??null)||(int)($d['payload']['id']??0)!==$d['source_id'])throw new ProtocolException('invalid_reference_data');
        return $d;
    }
    public function prepare(array $item): void
    {
        if($item['operation']==='revoke')return;
        $d=$this->decode($item);$m=self::find($this->site,$d['kind'],$d['source_id']);
        if($m&&$m->hasAttribute('is_sx_info_update')&&!$m->is_sx_info_update)return;
        $p=$d['payload'];
        $this->media->prepareStableReferences($p);
        if($d['kind']==='collections')self::required($this->site,$this->connection,'brands',(int)($p['brand_id']??0));
        foreach($p['category_ids']??[] as $id)self::required($this->site,$this->connection,'categories',(int)$id);
        $this->media->image($p['image']??null,in_array($d['kind'],['categories','brands'],true));
        foreach($p['images']??[] as $image)$this->media->image($image);
        foreach($p['enums']??[] as $enum)$this->media->image($enum['image']??null);
    }
    private function save($model): void
    {
        if(!$model->save())throw new \RuntimeException('Не удалось сохранить справочник: '.json_encode($model->errors,JSON_UNESCAPED_UNICODE));
    }
    public function apply(array $item,array $state): array
    {
        // References can still belong to local/protected products: never cascade-delete them.
        if($item['operation']==='revoke')return ['outcome'=>'kept'];
        $d=$this->decode($item);$kind=$d['kind'];$p=$d['payload'];$m=self::find($this->site,$kind,$d['source_id']);$created=!$m;
        \Yii::$app->db->createCommand()->update('{{%shop_gpd_reference_state}}',['kind'=>$kind,'source_id'=>$d['source_id']],['connection_id'=>$this->connection,'product_id'=>$item['id']])->execute();
        if($m&&$m->hasAttribute('is_sx_info_update')&&!$m->is_sx_info_update)return ['outcome'=>'protected'];
        if(!$m){$class=self::CLASSES[$kind];$m=new $class();$m->sx_id=$d['source_id'];if($m->hasAttribute('cms_site_id'))$m->cms_site_id=$this->site;}
        foreach(['name','description_short','description_full','country_alpha2','website_url','priority','is_adult'] as $field)if($m->hasAttribute($field)&&array_key_exists($field,$p))$m->$field=$p[$field];
        if($kind==='categories') {
            $m->shop_has_collections=(int)($p['has_collections']??0);
            if($created) {
                $site=\skeeks\cms\shop\models\CmsSite::findOne($this->site);$parent=$site->shopSite->catalogMainCmsTree;
                if(!$parent)throw new \RuntimeException('Не настроен корневой раздел каталога.');
                if($m->appendTo($parent)===false)throw new \RuntimeException('Не удалось создать раздел.');
            }
        } elseif($kind==='collections') {
            $brand=self::required($this->site,$this->connection,'brands',(int)($p['brand_id']??0));$m->shop_brand_id=$brand?$brand->id:null;
        } elseif($kind==='properties') {
            $types=['list'=>\skeeks\cms\relatedProperties\propertyTypes\PropertyTypeList::class,'number'=>\skeeks\cms\relatedProperties\propertyTypes\PropertyTypeNumber::class,'bool'=>\skeeks\cms\relatedProperties\propertyTypes\PropertyTypeBool::class,'string'=>\skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText::class];
            if(!isset($types[$p['type']??'']))throw new ProtocolException('unsupported_property_type');
            $component=$types[$p['type']];
            if(!$created&&$m->component!==$component)throw new \RuntimeException('Изменение типа используемой характеристики требует миграции значений.');
            $m->component=$component;$m->cmsContents=[\Yii::$app->shop->contentProducts->id];
            foreach(['is_multiple','is_offer_property','is_img_offer_property'] as $field)$m->$field=(int)($p[$field]??0);
            if($p['type']==='list')$m->component_settings=['fieldElement'=>$m->is_multiple?'selectMulti':'select'];
            // Cache the configured handler before Serialize encodes component_settings in beforeSave.
            $handler=$m->handler;
            if($p['type']==='list')$handler->fieldElement=$m->is_multiple?'selectMulti':'select';
            $m->cms_measure_code=$p['measure_code']?:null;
            $trees=[];foreach($p['category_ids']??[] as $id)$trees[]=self::required($this->site,$this->connection,'categories',(int)$id)->id;
            $m->cmsTrees=$trees;
        }
        $imageField=['categories'=>'image_id','brands'=>'logo_image_id','collections'=>'cms_image_id'][$kind]??null;
        if($imageField&&array_key_exists('image',$p))$m->$imageField=$this->media->image($p['image'],in_array($kind,['categories','brands'],true));
        $this->save($m);
        if($kind==='collections'&&array_key_exists('images',$p)) {
            $ids=[];foreach($p['images'] as $image)$ids[]=$this->media->image($image);
            $m->setImageIds(array_values(array_unique($ids)));$this->save($m);
        }
        if($kind==='properties')foreach($p['enums']??[] as $value) {
            if(!is_array($value)||empty($value['id']))throw new ProtocolException('invalid_property_enum');
            $e=CmsContentPropertyEnum::find()->andWhere(['property_id'=>$m->id,'sx_id'=>$value['id']])->one()?:new CmsContentPropertyEnum(['property_id'=>$m->id,'sx_id'=>$value['id']]);
            $e->value=(string)$value['value'];$e->value_for_saved_filter=$value['value_for_saved_filter']??null;$e->cms_image_id=$this->media->image($value['image']??null);$this->save($e);
        }
        return ['outcome'=>$created?'created':'updated'];
    }
}
