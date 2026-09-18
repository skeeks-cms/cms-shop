<?php
namespace skeeks\cms\shop\gpd;

use skeeks\cms\shop\models\ShopStore;

/** Full v2 store directory. Local pricing, activity and unrelated warehouses are preserved. */
final class StoreSync
{
    private $site;private $transport;private $image;private $checkpoint;private $rows;
    public function __construct(int $site,CatalogTransportInterface $transport,callable $image,callable $checkpoint)
    {$this->site=$site;$this->transport=$transport;$this->image=$image;$this->checkpoint=$checkpoint;}
    private function validate(array $row): void
    {
        if(!is_int($row['id']??null)||$row['id']<1||!is_string($row['name']??null)||trim($row['name'])===''||!array_key_exists('image',$row)||!is_array($row['image'])||!array_key_exists('address',$row)||($row['address']!==null&&!is_string($row['address'])))throw new ProtocolException('invalid_store');
        foreach(['latitude','longitude'] as $key)if(!array_key_exists($key,$row)||($row[$key]!==null&&(!is_numeric($row[$key])||!is_finite((float)$row[$key]))))throw new ProtocolException('invalid_store_coordinates');
    }
    public function rows(): array
    {
        if($this->rows===null){
            ($this->checkpoint)();$rows=$this->transport->request('GET','stores');
            if(array_values($rows)!==$rows||count($rows)>10000)throw new ProtocolException('invalid_stores');
            $seen=[];foreach($rows as $row){if(!is_array($row))throw new ProtocolException('invalid_store');$this->validate($row);if(isset($seen[$row['id']]))throw new ProtocolException('duplicate_store');$seen[$row['id']]=true;}
            usort($rows,static fn($a,$b)=>$a['id']<=>$b['id']);$this->rows=$rows;
        }
        return $this->rows;
    }
    public function apply(array $row): string
    {
        $this->validate($row);($this->checkpoint)();
        // Downloads happen before the destination write transaction.
        $image=($this->image)($row['image']);
        return \Yii::$app->db->transaction(function()use($row,$image){
            $matches=ShopStore::find()->where(['cms_site_id'=>$this->site,'sx_id'=>$row['id']])->limit(2)->all();
            if(count($matches)>1)throw new \RuntimeException('Дубли привязки склада GPD #'.$row['id']);
            $model=$matches[0]??new ShopStore(['cms_site_id'=>$this->site,'sx_id'=>$row['id']]);$created=$model->isNewRecord;
            $model->name=trim($row['name']);$model->address=trim((string)$row['address'])?:null;
            $model->latitude=(float)$row['latitude'];$model->longitude=(float)$row['longitude'];
            $model->is_supplier=1;$model->cms_image_id=$image;
            $owned=['name','address','latitude','longitude','is_supplier','cms_image_id'];
            if(!$created){
                foreach($model->getDirtyAttributes($owned) as $attribute=>$value){
                    $old=$model->getOldAttribute($attribute);
                    if(is_scalar($old)&&is_scalar($value)&&(string)$old===(string)$value)$model->setAttribute($attribute,$old);
                    elseif(in_array($attribute,['latitude','longitude'],true)&&(float)$old===(float)$value)$model->setAttribute($attribute,$old);
                }
                if(!$model->getDirtyAttributes($owned))return 'unchanged';
            }
            if(!$model->save(true,$created?null:$owned))throw new \RuntimeException('Не удалось сохранить склад GPD #'.$row['id'].': '.json_encode($model->errors,JSON_UNESCAPED_UNICODE));
            return $created?'created':'updated';
        });
    }
}