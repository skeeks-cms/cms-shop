<?php
namespace skeeks\cms\shop\gpd;

use yii\db\Connection;
use yii\db\Query;
use skeeks\cms\models\CmsSavedFilter;

/** Remove only the confirmed reference; products and accounting records are never deleted. */
final class ReferenceRemoval
{
    private $db;
    public function __construct(Connection $db) {$this->db=$db;}
    public function hasProducts($model,string $kind): bool
    {
        $q=(new Query())->select(['p.id','ce.sx_id'])->from(['ce'=>'{{%cms_content_element}}'])->innerJoin(['p'=>'{{%shop_product}}'],'p.id=ce.id');
        if($kind==='brands')$q->where(['or',['p.brand_id'=>$model->id],['exists',(new Query())->select('pc.shop_product_id')->from(['pc'=>'{{%shop_product2collection}}'])->innerJoin(['c'=>'{{%shop_collection}}'],'c.id=pc.shop_collection_id')->where('pc.shop_product_id=p.id')->andWhere(['c.shop_brand_id'=>$model->id])]]);
        else $q->innerJoin(['pc'=>'{{%shop_product2collection}}'],'pc.shop_product_id=p.id')->where(['pc.shop_collection_id'=>$model->id]);
        $cmd=$q->createCommand($this->db);
        $rows=$this->db->createCommand($cmd->sql.' FOR UPDATE',$cmd->params)->queryAll();
        return (bool)$rows;
    }
    public function otherSite($model,string $kind,int $site): bool
    {
        $q=(new Query())->from(['ce'=>'{{%cms_content_element}}'])->innerJoin(['p'=>'{{%shop_product}}'],'p.id=ce.id')->where(['or',['<>','ce.cms_site_id',$site],['ce.cms_site_id'=>null]]);
        if($kind==='brands')$q->andWhere(['or',['p.brand_id'=>$model->id],['exists',(new Query())->select('pc.shop_product_id')->from(['pc'=>'{{%shop_product2collection}}'])->innerJoin(['c'=>'{{%shop_collection}}'],'c.id=pc.shop_collection_id')->where('pc.shop_product_id=p.id')->andWhere(['c.shop_brand_id'=>$model->id])]]);
        else $q->innerJoin(['pc'=>'{{%shop_product2collection}}'],'pc.shop_product_id=p.id')->andWhere(['pc.shop_collection_id'=>$model->id]);
        if($q->exists($this->db))return true;
        return $kind==='brands' && CmsSavedFilter::find()->where(['shop_brand_id'=>$model->id])->andWhere(['or',['<>','cms_site_id',$site],['cms_site_id'=>null]])->exists();
    }
    public function remove($model,string $kind,int $site,bool $related): bool
    {
        if(!$this->db->getTransaction())throw new \LogicException('Reference removal requires a transaction.');
        $id=(int)$model->id;
        $this->db->createCommand('SELECT id FROM '.$model::tableName().' WHERE id=:id FOR UPDATE',[':id'=>$id])->queryScalar();
        $products=(new Query())->select(['p.id','ce.cms_site_id','ce.sx_id'])->from(['p'=>'{{%shop_product}}'])
            ->innerJoin(['ce'=>'{{%cms_content_element}}'],'ce.id=p.id');
        if($kind==='brands')$products->where(['p.brand_id'=>$id]);
        else $products->innerJoin(['pc'=>'{{%shop_product2collection}}'],'pc.shop_product_id=p.id')->where(['pc.shop_collection_id'=>$id]);
        $cmd=$products->createCommand($this->db);
        $links=$this->db->createCommand($cmd->sql.' FOR UPDATE',$cmd->params)->queryAll();
        if($links)return false;
        // A brand cannot own-delete another collection without that collection's own confirmed revoke.
        if($kind==='brands' && (new Query())->from('{{%shop_collection}}')->where(['shop_brand_id'=>$id])->exists($this->db))return false;
        if($kind==='brands')$this->db->createCommand('SELECT id FROM {{%cms_saved_filter}} WHERE shop_brand_id=:id FOR UPDATE',[':id'=>$id])->queryAll();
        $filters=$kind==='brands'?CmsSavedFilter::find()->where(['shop_brand_id'=>$id])->all():[];
        foreach($filters as $filter)if((int)$filter->cms_site_id!==$site)return false;
        if(!$related && ($links || $filters))return false;
        if($related) {
            foreach($filters as $filter)if($filter->delete()===false)throw new \RuntimeException('Удаление сохранённого фильтра отменено.');
        }
        if($model->delete()===false)throw new \RuntimeException('Удаление справочника отменено.');
        return true;
    }
}
