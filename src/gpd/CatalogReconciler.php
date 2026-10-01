<?php
namespace skeeks\cms\shop\gpd;

use yii\db\Query;

/** Bounded audit of actual local identities, including records imported through v1. */
final class CatalogReconciler
{
    private $site;private $connection;private $catalog;private $references;private $catalogReceiver;private $referenceReceiver;private $catalogApplier;private $referenceApplier;
    public function __construct(int $site,string $connection,CatalogTransportInterface $catalog,CatalogTransportInterface $references,CatalogWriterInterface $writer,CatalogWriterInterface $referenceWriter)
    {
        $this->site=$site;$this->connection=$connection;$this->catalog=$catalog;$this->references=$references;
        $db=\Yii::$app->db;
        $this->catalogReceiver=new CatalogReceiver($db,$connection,$catalog);
        $this->referenceReceiver=new CatalogReceiver($db,$connection,$references,'references');
        $base=$this->catalogReceiver->state();
        $this->referenceReceiver->register($site,$base['source_url'],$base['credential_fingerprint']);
        $this->catalogApplier=new CatalogApplier($db,$connection,$catalog,$writer);
        $this->referenceApplier=new CatalogApplier($db,$connection,$references,$referenceWriter,'references');
    }
    private function query(string $kind): Query
    {
        if($kind==='products')return (new Query())->select(['ce.id','ce.sx_id'])->from(['ce'=>'{{%cms_content_element}}'])->innerJoin(['p'=>'{{%shop_product}}'],'p.id=ce.id')->where(['ce.cms_site_id'=>$this->site])->andWhere(['>','ce.sx_id',0]);
        if(!in_array($kind,['brands','collections'],true))throw new ProtocolException('invalid_reference');
        return (new Query())->select(['id','sx_id'])->from('{{%shop_'.($kind==='brands'?'brand':'collection').'}}')->where(['>','sx_id',0]);
    }
    public function upper(string $kind): int {return (int)$this->query($kind)->max($kind==='products'?'ce.id':'id');}
    public function remaining(string $kind,int $after,int $upper): int
    {
        $column=$kind==='products'?'ce.id':'id';
        return (int)$this->query($kind)->andWhere(['>',$column,$after])->andWhere(['<=',$column,$upper])->count();
    }
    private function fetch(CatalogTransportInterface $transport,string $endpoint,string $key,array $values): array
    {
        try{return $transport->request('POST',$endpoint,[$key=>$values,'audit'=>true])['items']??[];}
        catch(ProtocolException $e) {
            if($e->reason!=='batch_too_large'||count($values)<2)throw $e;
            $half=(int)ceil(count($values)/2);
            return array_merge($this->fetch($transport,$endpoint,$key,array_slice($values,0,$half)),$this->fetch($transport,$endpoint,$key,array_slice($values,$half)));
        }
    }
    public function page(string $kind,int $after,int $upper,callable $checkpoint): array
    {
        $column=$kind==='products'?'ce.id':'id';
        $rows=$this->query($kind)->andWhere(['>',$column,$after])->andWhere(['<=',$column,$upper])->orderBy($column)->limit(20)->all();
        $stats=['checked'=>count($rows),'pending'=>0,'deleted'=>0,'deactivated'=>0,'kept'=>0,'updated'=>0,'unchanged'=>0,'protected'=>0,'other_source'=>0,'absent'=>0,'created'=>0];
        if(!$rows)return ['after'=>$after,'done'=>true,'counts'=>$stats];
        $ids=array_values(array_unique(array_map('intval',array_column($rows,'sx_id'))));
        if($kind==='products') {
            $items=$this->fetch($this->catalog,'batch','ids',$ids);
            $received=$this->catalogReceiver->acceptBatch($ids,$items,true);
            $table='{{%shop_gpd_catalog_state}}';$applier=$this->catalogApplier;
        } else {
            $identities=array_map(static fn($id)=>['kind'=>$kind,'source_id'=>$id],$ids);
            $response=$this->fetch($this->references,'resolve','references',$identities);
            if(count($response)!==count($identities))throw new ProtocolException('incomplete_batch');
            $items=[];$mapped=[];
            foreach($response as $item) {
                if(($item['kind']??null)!==$kind||!is_int($item['source_id']??null)||!in_array($item['source_id'],$ids,true)||isset($mapped[$item['source_id']]))throw new ProtocolException('invalid_reference');
                $mapped[$item['source_id']]=true;
                if(($item['status']??null)==='not_available'){++$stats['pending'];$stats['pending_not_available']=($stats['pending_not_available']??0)+1;continue;}
                $items[]=$item;
            }
            $received=$items?$this->referenceReceiver->acceptBatch(array_column($items,'id'),$items,true):['pending'=>0];
            $table='{{%shop_gpd_reference_state}}';$applier=$this->referenceApplier;
        }
        $stats['pending']+=$received['pending'];
        foreach($items as $item)if(isset($item['status'])) {
            $reason=$item['status']==='not_available'?'not_available':($item['reason']??'unknown');
            if(!in_array($reason,['not_available','publication_pending','access_change_pending'],true))$reason='unknown';
            $key='pending_'.$reason;$stats[$key]=($stats[$key]??0)+1;
        }
        foreach($items as $item) {
            $checkpoint();if(isset($item['status']))continue;
            $state=(new Query())->from($table)->where(['connection_id'=>$this->connection,'product_id'=>$item['id']])->one();
            try {$result=$applier->apply(['state'=>$state,'item'=>$item]);}
            catch(ReferencePendingException $e){$result=['outcome'=>'pending'];$stats['pending_dependencies']=($stats['pending_dependencies']??0)+1;}
            ++$stats[$result['outcome']];
        }
        return ['after'=>(int)end($rows)['id'],'done'=>false,'counts'=>$stats];
    }
}
