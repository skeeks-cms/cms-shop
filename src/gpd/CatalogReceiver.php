<?php
namespace skeeks\cms\shop\gpd;

use yii\db\Connection;
use yii\db\Query;

/** Durable receipt of catalog metadata. Receipt is NOT application to the shop. */
final class CatalogReceiver
{
    private $db;
    private $id;
    private $transport;
    public function __construct(Connection $db, string $id, CatalogTransportInterface $transport)
    {
        $this->db=$db; $this->id=$id; $this->transport=$transport;
    }
    private function query(): Query { return (new Query())->from('{{%shop_gpd_connection}}')->where(['id'=>$this->id]); }
    public function state(): array
    {
        $row=$this->query()->one($this->db);
        if (!$row) throw new ProtocolException('connection_not_registered');
        return $row;
    }
    public function register(int $site, string $url, string $fingerprint): void
    {
        if (!preg_match('/^[a-z0-9][a-z0-9_.-]{0,99}$/D',$this->id) || $site<1 || strlen($url)>512 || strlen($fingerprint)!==64) {
            throw new ProtocolException('invalid_connection_configuration');
        }
        $this->db->createCommand()->upsert('{{%shop_gpd_connection}}',[
            'id'=>$this->id,'cms_site_id'=>$site,'source_url'=>rtrim($url,'/'),
            'credential_fingerprint'=>$fingerprint,'updated_at'=>time(),
        ],false)->execute();
        $row=$this->state();
        if ((int)$row['cms_site_id']!==$site || $row['source_url']!==rtrim($url,'/') || !hash_equals($row['credential_fingerprint'],$fingerprint)) {
            throw new ProtocolException('connection_identity_changed');
        }
    }
    private function atomic(array $expected, callable $callback): void
    {
        $this->db->transaction(function()use($expected,$callback){
            $row=$this->db->createCommand('SELECT * FROM {{%shop_gpd_connection}} WHERE id=:id FOR UPDATE',[':id'=>$this->id])->queryOne();
            foreach (['phase','cursor','generation'] as $field) {
                if ((string)$row[$field] !== (string)$expected[$field]) throw new ProtocolException('concurrent_receiver');
            }
            $callback();
        });
    }
    private function update(array $values): void
    {
        $this->db->createCommand()->update('{{%shop_gpd_connection}}',$values+['updated_at'=>time()],['id'=>$this->id])->execute();
    }
    private function token($value): string
    {
        if (!is_string($value) || $value==='' || strlen($value)>8192) throw new ProtocolException('invalid_response_cursor');
        return $value;
    }
    private function number($value): string
    {
        if (!is_string($value) || !preg_match('/^[1-9][0-9]{0,18}$/D',$value) || self::compare($value,(string)PHP_INT_MAX)>0) {
            throw new ProtocolException('invalid_revision');
        }
        return $value;
    }
    private static function compare(string $a,string $b): int {return strlen($a)<=>strlen($b) ?: strcmp($a,$b);}
    private function items($items, bool $positions=false): array
    {
        if (!is_array($items) || array_values($items)!==$items || count($items)>100) throw new ProtocolException('invalid_items');
        $seen=[]; $last='0';
        foreach ($items as $item) {
            if (!is_array($item) || !is_int($item['id']??null) || $item['id']<1) throw new ProtocolException('invalid_product_id');
            $this->number($item['revision']??null);
            if ($positions) {
                $position=$this->number($item['position']??null);
                if (self::compare($position,$last)<=0) throw new ProtocolException('invalid_event_order');
                $last=$position;
            } elseif (isset($seen[$item['id']])) throw new ProtocolException('duplicate_product');
            $seen[$item['id']]=true;
            if (($item['status']??null)==='pending') continue;
            if (!in_array($item['operation']??null,['upsert','revoke'],true)) throw new ProtocolException('invalid_operation');
            if ($item['operation']==='upsert') $this->number($item['product_revision']??null);
        }
        return $items;
    }
    private function remember(array $item, int $generation, bool $seen): void
    {
        $key=['connection_id'=>$this->id,'product_id'=>$item['id']];
        $row=(new Query())->from('{{%shop_gpd_catalog_state}}')->where($key)->one($this->db);
        if ($seen && $row && (int)$row['seen_generation']===$generation) {
            throw new ProtocolException('duplicate_snapshot_product');
        }
        $values=['updated_at'=>time()];
        if ($seen) $values['seen_generation']=$generation;
        if (!$row || self::compare($item['revision'],(string)$row['revision'])>=0) {
            $values['revision']=$item['revision'];
            $values['needs_resolution']=($item['status']??null)==='pending' ? 1 : 0;
            // Pending cannot erase a previously received state; it is explicitly unresolved.
            if (!$values['needs_resolution']) {
                $values['operation']=$item['operation'];
                $values['product_revision']=$item['product_revision']??null;
            }
        }
        $this->db->createCommand()->upsert('{{%shop_gpd_catalog_state}}',$key+$values,$values)->execute();
    }
    /** One bounded API page, committed together with its cursor. */
    public function receive(): array
    {
        $state=$this->state();
        try {
            if ($state['phase']==='bootstrap') {
                $status=$this->transport->request('GET','status');
                if (($status['protocol']??null)!==1 || ($status['stream']??null)!=='catalog' || ($status['seeded']??null)!==true) {
                    throw new ProtocolException('unsupported_or_unready_catalog');
                }
                $page=$this->transport->request('POST','bootstrap');
                $cursor=$this->token($page['cursor']??null); $changes=$this->token($page['changes_cursor']??null);
                if (!is_int($page['total']??null) || $page['total']<0) throw new ProtocolException('invalid_total');
                $this->atomic($state,function()use($state,$page,$cursor,$changes){
                    $this->update(['phase'=>'snapshot','cursor'=>$cursor,'changes_cursor'=>$changes,
                        'generation'=>(int)$state['generation']+1,'total'=>$page['total'],'received'=>0]);
                });
                return ['count'=>0,'more'=>true,'stage'=>'bootstrap'];
            }
            $snapshot=$state['phase']==='snapshot';
            if (!$snapshot && $state['phase']!=='changes') throw new ProtocolException('invalid_phase');
            $page=$this->transport->request('GET',$snapshot?'manifest':'changes',['cursor'=>$state['cursor'],'limit'=>50]);
            $items=$this->items($page['items']??null,!$snapshot); $next=$this->token($page['next_cursor']??null);
            $flag=$snapshot?'done':'has_more';
            if (!is_bool($page[$flag]??null)) throw new ProtocolException('invalid_page_boundary');
            $more=$snapshot?!$page['done']:$page['has_more'];
            if (($items || $more) && $next===$state['cursor']) throw new ProtocolException('cursor_did_not_advance');
            if ($more && !$items) throw new ProtocolException('empty_incomplete_page');
            if ($snapshot) {
                if (($page['total']??null)!==(int)$state['total'] || ($page['changes_cursor']??null)!==$state['changes_cursor']) throw new ProtocolException('snapshot_boundary_changed');
                $received=(int)$state['received']+count($items);
                if ($received>(int)$state['total'] || (!$more && $received!==(int)$state['total'])) throw new ProtocolException('incomplete_snapshot');
            }
            $this->atomic($state,function()use($state,$items,$snapshot,$more,$next){
                foreach ($items as $item) $this->remember($item,(int)$state['generation'],$snapshot);
                $values=['cursor'=>$next];
                if ($snapshot) {
                    $values['received']=(int)$state['received']+count($items);
                    if (!$more) {
                        // Absence is only a request for explicit batch verification, never a revoke.
                        $this->db->createCommand()->update('{{%shop_gpd_catalog_state}}',['needs_resolution'=>1],[
                            'and',['connection_id'=>$this->id],['<','seen_generation',(int)$state['generation']],
                        ])->execute();
                        $values['phase']='changes'; $values['cursor']=$state['changes_cursor'];
                    }
                }
                $this->update($values);
            });
            return ['count'=>count($items),'more'=>$snapshot||$more,'stage'=>$snapshot?'snapshot':'changes'];
        } catch (ProtocolException $e) {
            if (!in_array($e->reason,['cursor_expired','snapshot_expired'],true)) throw $e;
            $this->atomic($state,function(){ $this->update(['phase'=>'bootstrap','cursor'=>null,'changes_cursor'=>null]); });
            return ['count'=>0,'more'=>true,'stage'=>'recovery'];
        }
    }
    /** Visit each unresolved ID once per job. Native jobs own retries and scheduling. */
    public function resolve(int $after=0): array
    {
        $state=$this->state();
        $ids=(new Query())->select('product_id')->from('{{%shop_gpd_catalog_state}}')->where(['connection_id'=>$this->id,'needs_resolution'=>1])
            ->andWhere(['>','product_id',$after])->orderBy('product_id')->limit(20)->column($this->db);
        if (!$ids) return ['count'=>0,'after'=>$after,'more'=>false];
        $ids=array_map('intval',$ids);
        $page=$this->transport->request('POST','batch',['ids'=>$ids]);
        $items=$page['items']??null;
        if (!is_array($items) || count($items)!==count($ids)) throw new ProtocolException('incomplete_batch');
        $seen=[];
        foreach ($items as $item) {
            if (!is_int($item['id']??null) || !in_array($item['id'],$ids,true) || isset($seen[$item['id']])) throw new ProtocolException('invalid_batch_ids');
            $seen[$item['id']]=true;
            if (($item['status']??null)==='not_available') continue;
            $this->items([$item]);
        }
        $this->atomic($state,function()use($items,$state){
            foreach ($items as $item) {
                // not_available has no ordered revision: never infer deletion from it.
                if (($item['status']??null)!=='not_available') $this->remember($item,(int)$state['generation'],false);
            }
        });
        return ['count'=>count($ids),'after'=>max($ids),'more'=>true];
    }
    public function summary(): array
    {
        $state=$this->state();
        $base=(new Query())->from('{{%shop_gpd_catalog_state}}')->where(['connection_id'=>$this->id]);
        return ['mode'=>'receipt_only','phase'=>$state['phase'],'snapshot_received'=>(int)$state['received'],'snapshot_total'=>(int)$state['total'],
            'tracked'=>(int)(clone $base)->count('*',$this->db),
            'unresolved'=>(int)(clone $base)->andWhere(['needs_resolution'=>1])->count('*',$this->db),
            'applied_to_shop'=>false];
    }
}
