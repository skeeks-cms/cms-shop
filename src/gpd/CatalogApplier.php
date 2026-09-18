<?php
namespace skeeks\cms\shop\gpd;

use yii\db\Query;
use yii\db\Expression;
use yii\db\Connection;

/** Applied revision is committed with the shop write, never with the HTTP fetch. */
final class CatalogApplier
{
    private $db; private $stateTable; private $id; private $transport; private $writer;
    public function __construct(Connection $db,string $id,CatalogTransportInterface $transport,CatalogWriterInterface $writer, string $stream = 'catalog')
    {$this->db=$db;$this->id=$id;$this->transport=$transport;$this->writer=$writer; if(!in_array($stream,['catalog','references','offers'],true))throw new ProtocolException('invalid_stream'); $this->stateTable=['catalog'=>'{{%shop_gpd_catalog_state}}','references'=>'{{%shop_gpd_reference_state}}','offers'=>'{{%shop_gpd_offer_state}}'][$stream];}
    private function pending(): Query
    {
        return (new Query())->from($this->stateTable)->where(['connection_id'=>$this->id,'needs_resolution'=>0])
            ->andWhere(['in','operation',['upsert','revoke']])->andWhere('[[revision]] > [[applied_revision]]');
    }
    public function remaining(int $after=0): int {return (int)$this->pending()->andWhere(['>','product_id',$after])->count('*',$this->db);}
    public function page(int $after=0): array
    {
        $rows=$this->pending()->andWhere(['>','product_id',$after])->orderBy('product_id')->limit(10)->all($this->db);
        if (!$rows) return [];
        $ids=array_map('intval',array_column($rows,'product_id'));
        $items=$this->fetch($ids); $mapped=[];
        foreach($items as $item) {
            if(!is_array($item)||!is_int($item['id']??null)||!in_array($item['id'],$ids,true)||isset($mapped[$item['id']])) throw new ProtocolException('invalid_batch_ids');
            $mapped[$item['id']]=$item;
        }
        if(count($mapped)!==count($ids)) throw new ProtocolException('incomplete_batch');
        return array_map(fn($row)=>['state'=>$row,'item'=>$mapped[$row['product_id']]],$rows);
    }
    private function fetch(array $ids): array
    {
        try {return $this->transport->request('POST','batch',['ids'=>$ids])['items']??[];}
        catch(ProtocolException $e) {
            if($e->reason!=='batch_too_large'||count($ids)===1)throw $e;
            $half=(int)ceil(count($ids)/2);
            return array_merge($this->fetch(array_slice($ids,0,$half)),$this->fetch(array_slice($ids,$half)));
        }
    }
    public function apply(array $entry): array
    {
        $item=$entry['item']; $expected=$entry['state'];
        if(isset($item['status'])) return ['outcome'=>'pending'];
        foreach(['revision'] as $field) {
            if(!is_string($item[$field]??null)||!preg_match('/^[1-9][0-9]{0,18}$/D',$item[$field])||(strlen($item[$field])===strlen((string)PHP_INT_MAX)&&strcmp($item[$field],(string)PHP_INT_MAX)>0))throw new ProtocolException('invalid_revision');
        }
        if(!in_array($item['operation']??null,['upsert','revoke'],true))throw new ProtocolException('invalid_operation');
        if((int)$item['revision']<(int)$expected['revision'])return ['outcome'=>'pending'];
        if($item['operation']==='upsert' && (!is_array($item['data']??null)||(int)($item['data']['id']??0)!==$item['id']||!is_string($item['product_revision']??null)||!preg_match('/^[1-9][0-9]{0,18}$/D',$item['product_revision'])))throw new ProtocolException('invalid_product_data');
        if((string)$item['revision']===(string)$expected['revision'] && ($item['operation']!==$expected['operation'] || ($item['operation']==='upsert' && (string)$item['product_revision']!==(string)$expected['product_revision'])))throw new ProtocolException('conflicting_revision');
        $this->writer->prepare($item);
        return $this->db->transaction(function()use($expected,$item){
            $row=$this->db->createCommand('SELECT * FROM '.$this->stateTable.' WHERE connection_id=:c AND product_id=:p FOR UPDATE',[':c'=>$this->id,':p'=>$item['id']])->queryOne();
            if(!$row||$row['needs_resolution']||(string)$row['revision']!==(string)$expected['revision'])return ['outcome'=>'pending'];
            if((int)$row['applied_revision']>=(int)$item['revision'])return ['outcome'=>'unchanged'];
            $result=$this->writer->apply($item,$row);
            if(($result['outcome']??'')==='pending')return $result;
            $values=['applied_revision'=>$item['revision'],'applied_at'=>time()];
            foreach(['local_product_id','deactivated_by_gpd'] as $field)if(array_key_exists($field,$result))$values[$field]=$result[$field];
            $this->db->createCommand()->update($this->stateTable,$values,['connection_id'=>$this->id,'product_id'=>$item['id']])->execute();
            return $result;
        });
    }
}
