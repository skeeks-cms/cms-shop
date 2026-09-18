<?php
namespace skeeks\cms\shop\gpd;
use yii\db\Query;

/** Same source/markup/priority rules as store-price maintenance, bounded to one product. */
final class OfferPrices
{
    public function recalculate(int $site,int $product): void
    {
        $db=\Yii::$app->db;
        $row=(new Query())->select(['i.purchase_price','i.selling_price','s.source_purchase_price','s.source_selling_price','s.purchase_extra_charge','s.selling_extra_charge'])
            ->from(['i'=>'{{%shop_store_product}}'])->innerJoin(['s'=>'{{%shop_store}}'],'s.id=i.shop_store_id')
            ->where(['i.shop_product_id'=>$product,'s.cms_site_id'=>$site,'i.is_active'=>1,'s.is_active'=>1])
            ->andWhere(['or',['s.is_supplier'=>1],['s.is_sync_external'=>1]])
            ->orderBy(new \yii\db\Expression('CASE WHEN i.quantity>0 THEN 0 ELSE 1 END, s.priority, s.id'))->one();
        $types=(new Query())->from('{{%shop_type_price}}')->where(['cms_site_id'=>$site])->all();
        foreach($types as $type){
            if(!$type['is_default']&&!$type['is_purchase'])continue;
            $prefix=$type['is_purchase']?'purchase':'selling';
            $source=$row?$row['source_'.$prefix.'_price']:null;
            $value=$row?(float)$row[$source==='purchase_price'?'purchase_price':'selling_price']*(float)$row[$prefix.'_extra_charge']/100:0;
            $this->price($product,(int)$type['id'],round($value),'RUB');
        }
        // Topological passes also support price types based on another automatic type.
        $pending=array_column(array_filter($types,static fn($t)=>$t['is_auto']),null,'id');
        for($pass=0,$limit=count($pending);$pending&&$pass<$limit;++$pass){
            foreach($pending as $id=>$type){
                $base=(int)$type['base_auto_shop_type_price_id'];if(isset($pending[$base]))continue;
                $p=(new Query())->from('{{%shop_product_price}}')->where(['product_id'=>$product,'type_price_id'=>$base])->one();
                if($p)$this->price($product,(int)$id,round((float)$p['price']*(float)$type['auto_extra_charge']/100),$p['currency_code']);
                unset($pending[$id]);
            }
        }
        if($pending)throw new \RuntimeException('Циклическая зависимость автоматических цен.');
    }
    private function price(int $product,int $type,float $value,string $currency): void
    {
        $db=\Yii::$app->db;$key=['product_id'=>$product,'type_price_id'=>$type];
        $row=(new Query())->from('{{%shop_product_price}}')->where($key)->one();
        if(!$row)$db->createCommand()->insert('{{%shop_product_price}}',$key+['price'=>$value,'currency_code'=>$currency])->execute();
        elseif(!$row['is_fixed'])$db->createCommand()->update('{{%shop_product_price}}',['price'=>$value,'currency_code'=>$currency],$key+['is_fixed'=>0])->execute();
    }
}