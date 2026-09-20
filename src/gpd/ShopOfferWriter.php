<?php
namespace skeeks\cms\shop\gpd;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopStore;
use skeeks\cms\shop\models\ShopStoreProduct;
use yii\db\Query;

/** Complete bundles affect only GPD-linked warehouses of this site. */
final class ShopOfferWriter implements CatalogWriterInterface
{
    private $site;private $checkpoint;
    public function __construct(int $site,callable $checkpoint){$this->site=$site;$this->checkpoint=$checkpoint;}
    private function product(int $id)
    {
        $rows=ShopCmsContentElement::find()->where(['cms_site_id'=>$this->site,'sx_id'=>$id])->limit(2)->all();
        if(count($rows)>1)throw new \RuntimeException('Дубли товара GPD #'.$id);
        return $rows[0]??null;
    }
    private function stores(): array
    {return ShopStore::find()->where(['cms_site_id'=>$this->site])->andWhere(['>','sx_id',0])->all();}
    private function bundle(array $item): array
    {
        $d=$item['data']??[];$p=$d['payload']??[];
        if(($d['kind']??null)!=='offers'||($d['source_id']??null)!==$item['id']||($p['id']??null)!==$item['id']||($p['complete']??null)!==true||($p['currency']??null)!=='RUB'||!is_array($p['offers']??null)||array_values($p['offers'])!==$p['offers'])throw new ProtocolException('invalid_offer_bundle');
        $seen=[];
        foreach($p['offers'] as $r){
            if(!is_array($r)||!is_int($r['store_id']??null)||$r['store_id']<1||isset($seen[$r['store_id']])||!is_string($r['supplier_code']??null)||!is_string($r['supplier_name']??null)||!is_bool($r['is_active']??null))throw new ProtocolException('invalid_offer');
            $seen[$r['store_id']]=true;
            foreach(['quantity','purchase_price','selling_price'] as $f)if(!is_numeric($r[$f]??null)||!is_finite((float)$r[$f])||($f!=='quantity'&&$r[$f]<0))throw new ProtocolException('invalid_offer_amount');
        }
        return $p['offers'];
    }
    public function prepare(array $item): void
    {if($item['operation']==='upsert')$this->bundle($item);}
    private function save(ShopStoreProduct $m): void
    {if(!$m->save())throw new \RuntimeException('Не удалось сохранить складскую позицию: '.json_encode($m->errors,JSON_UNESCAPED_UNICODE));}
    public function apply(array $item,array $state): array
    {
        ($this->checkpoint)();$product=$this->product($item['id']);
        if(!$product){if($item['operation']==='revoke')return ['outcome'=>'kept'];throw new ReferencePendingException('Ожидается товар GPD #'.$item['id']);}
        $offers=$item['operation']==='upsert'?$this->bundle($item):[];$stores=[];
        foreach($this->stores() as $store){if(isset($stores[$store->sx_id]))throw new \RuntimeException('Дубли привязки склада GPD #'.$store->sx_id);$stores[$store->sx_id]=$store;}
        foreach($offers as $r)if(!isset($stores[$r['store_id']]))throw new ReferencePendingException('Настройте склад GPD #'.$r['store_id']);
        $kept=[];$changed=false;$previousProducts=[];
        foreach($offers as $r){
            ($this->checkpoint)();$store=$stores[$r['store_id']];
            // Supplier code identifies the warehouse row, including previously
            // unbound imports. Source-side reassignment stays within this site.
            $m=null;
            if($r['supplier_code']!==''){
                $m=ShopStoreProduct::find()->where(['shop_store_id'=>$store->id,'external_id'=>$r['supplier_code']])->one();
                if($m&&$m->shop_product_id&&$m->shop_product_id!=$product->id){
                    $previous=ShopCmsContentElement::find()->where(['id'=>$m->shop_product_id,'cms_site_id'=>$this->site])->one();
                    if(!$previous)throw new \RuntimeException('Складская позиция связана с товаром другого сайта.');
                    $previousProducts[(int)$m->shop_product_id]=true;
                }
            }
            if(!$m)$m=ShopStoreProduct::find()->where(['shop_store_id'=>$store->id,'shop_product_id'=>$product->id])->orderBy(['id'=>SORT_ASC])->one();
            if($m&&$m->id){
                // Release the obsolete target row's unique warehouse/product slot.
                // Keep the supplier row and its identity instead of deleting it.
                $occupied=ShopStoreProduct::find()->where(['shop_store_id'=>$store->id,'shop_product_id'=>$product->id])->all();
                foreach($occupied as $other){
                    if($other->id==$m->id)continue;
                    $other->shop_product_id=null;$other->is_active=0;$other->quantity=0;
                    $this->save($other);$changed=true;
                }
            }
            if(!$m)$m=new ShopStoreProduct();
            $m->shop_store_id=$store->id;$m->shop_product_id=$product->id;$m->external_id=$r['supplier_code']?:null;$m->name=$r['supplier_name'];
            $m->quantity=$r['is_active']?(float)$r['quantity']:0;$m->is_active=(int)$r['is_active'];
            $m->purchase_price=(float)$r['purchase_price'];$m->selling_price=(float)$r['selling_price'];
            if($m->isNewRecord||$m->getDirtyAttributes()){$this->save($m);$changed=true;}
            $kept[]=$m->id;
        }
        if($stores){
            $q=ShopStoreProduct::find()->where(['shop_product_id'=>$product->id,'shop_store_id'=>array_map(static fn($s)=>$s->id,$stores)]);
            if($kept)$q->andWhere(['not in','id',$kept]);
            foreach($q->all() as $m){if($m->is_active||(float)$m->quantity!==0.0){$m->is_active=0;$m->quantity=0;$this->save($m);$changed=true;}}
        }
        foreach(array_keys($previousProducts) as $previousId)(new OfferPrices())->recalculate($this->site,$previousId);
        (new OfferPrices())->recalculate($this->site,(int)$product->id);
        return ['outcome'=>$changed?'updated':'unchanged','local_product_id'=>(int)$product->id];
    }
}