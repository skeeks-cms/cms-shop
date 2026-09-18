<?php
namespace skeeks\cms\shop\jobs;

use skeeks\cms\job\handlers\ChunkedJobHandler;
use skeeks\cms\job\runtime\JobContext;
use skeeks\cms\job\contracts\JobReporterInterface;
use skeeks\cms\job\exceptions\JobCancelledException;
use skeeks\cms\shop\gpd\CatalogReceiver;
use skeeks\cms\shop\gpd\CatalogApplier;
use skeeks\cms\shop\gpd\CatalogTransport;
use skeeks\cms\shop\gpd\ShopCatalogWriter;
use skeeks\cms\shop\gpd\ShopReferenceWriter;
use skeeks\cms\shop\gpd\ReferencePendingException;

class GpdOffersJobHandler extends ChunkedJobHandler
{
    public $maxRunSeconds=15;
    private $receiver;private $applier;private $stores;private $next;
    protected function loadChunk(JobContext $context,array $cursor)
    {
        if($context->getPayload())throw new \InvalidArgumentException('Expected empty payload.');
        return [$cursor];
    }
    protected function processChunk(array $items,JobContext $context,JobReporterInterface $reporter)
    {
        $site=(int)$context->getRun()->cms_site_id;$settings=\Yii::$app->gpd->forSite($site);
        if(!$settings->enabled||!\Yii::$app->gpdReceiver->offersEnabled){$this->next=null;$reporter->setStage('disabled','Синхронизация цен и остатков отключена.');return;}
        $checkpoint=static function()use($reporter){$reporter->heartbeat();if($reporter->isCancelled())throw new JobCancelledException('Синхронизация цен и остатков отменена.');};
        if(!$this->receiver) {
            $base=\Yii::$app->gpdReceiver->receiverForSite($site)->state();$api=\Yii::$app->get(\Yii::$app->gpdReceiver->apiComponent);
            $media=new ShopCatalogWriter($site,$settings,$api,$checkpoint);
            $this->stores=new \skeeks\cms\shop\gpd\StoreSync($site,new CatalogTransport($base['source_url'],(string)$api->api_key,null,'dictionaries'),static fn($image)=>$media->image($image,true),$checkpoint);
            $transport=new CatalogTransport($base['source_url'],(string)$api->api_key,null,'offers');
            $this->receiver=new CatalogReceiver(\Yii::$app->db,$base['id'],$transport,'offers');
            $this->receiver->register($site,$base['source_url'],$base['credential_fingerprint']);
            $writer=new \skeeks\cms\shop\gpd\ShopOfferWriter($site,$checkpoint);
            $this->applier=new CatalogApplier(\Yii::$app->db,$base['id'],$transport,$writer,'offers');
        }
        $cursor=$items[0];$phase=$cursor['phase']??'stores';
        $result=json_decode($context->getRun()->result_json??'{}',true)?:[];
        $counts=$result['counts']??['received'=>0,'created'=>0,'updated'=>0,'protected'=>0,'kept'=>0,'pending'=>0,'unchanged'=>0,'errors'=>0];
        $counts+=['stores_created'=>0,'stores_updated'=>0,'stores_unchanged'=>0];
        $checkpoint();
        if($phase==='stores') {
            $pending=array_values(array_filter($this->stores->rows(),static fn($row)=>$row['id']>(int)($cursor['after']??0)));
            $after=(int)($cursor['after']??0);
            foreach(array_slice($pending,0,20) as $row){
                $checkpoint();$after=$row['id'];
                try{$outcome=$this->stores->apply($row);++$counts['stores_'.$outcome];if($outcome==='unchanged')$reporter->countSkipped();else $reporter->countSuccess();}
                catch(JobCancelledException $e){throw $e;}
                catch(\Throwable $e){++$counts['errors'];$reporter->itemError('gpd_store',$row['id'],$e instanceof \yii\db\Exception?'Ошибка записи склада в БД.':$e->getMessage());}
                $reporter->advance();
            }
            $this->next=count($pending)>20?['phase'=>'stores','after'=>$after]:['phase'=>'receive'];
        } elseif($phase==='receive') {
            $r=$this->receiver->receive();$counts['received']+=$r['count'];
            $this->next=$r['more']?['phase'=>'receive']:['phase'=>'resolve','after'=>0];
        } elseif($phase==='resolve') {
            $r=$this->receiver->resolve((int)($cursor['after']??0));
            $this->next=$r['more']?['phase'=>'resolve','after'=>$r['after']]:['phase'=>'apply','after'=>0];
        } else {
            $page=$this->applier->page((int)($cursor['after']??0));
            foreach($page as $entry) {
                $checkpoint();
                try {
                    $r=$this->applier->apply($entry);++$counts[$r['outcome']];
                    if(in_array($r['outcome'],['created','updated'],true))$reporter->countSuccess();else $reporter->countSkipped();
                } catch(JobCancelledException $e){throw $e;}
                catch(ReferencePendingException $e){++$counts['pending'];$reporter->countSkipped();}
                catch(\Throwable $e){++$counts['errors'];$reporter->itemError('gpd_offer',$entry['item']['id'],$e instanceof \yii\db\Exception?'Ошибка сохранения цен и остатков в БД.':$e->getMessage());}
                $reporter->advance();
            }
            $this->next=$page?['phase'=>'apply','after'=>(int)end($page)['state']['product_id']]:null;
        }
        $message='Цены и остатки: получено пакетов '.$counts['received'].'; обновлено товаров '.$counts['updated'].'; без изменений '.$counts['unchanged'].'; ожидают товара или склада '.$counts['pending'].'; ошибок '.$counts['errors'].'.';
        if($phase==='stores')$message='Склады: создано '.$counts['stores_created'].'; обновлено '.$counts['stores_updated'].'; без изменений '.$counts['stores_unchanged'].'; ошибок '.$counts['errors'].'.';
        $message.=' Склады: создано '.$counts['stores_created'].'; обновлено '.$counts['stores_updated'].'.';
        $reporter->setStage($phase,$message);$reporter->setResult(['counts'=>$counts,'remaining'=>$this->applier->remaining(),'message'=>$message]);
    }
    protected function finish(JobContext $context,JobReporterInterface $reporter)
    {
        if(!$this->applier)return;
        $result=json_decode($context->getRun()->result_json??'{}',true)?:[];
        $result['remaining']=$this->applier->remaining();
        if($result['remaining']>0)$reporter->countWarning($result['remaining']);
        $reporter->setTotal((int)$context->getRun()->progress_current);
        $reporter->setResult($result);$reporter->setStage('complete',$result['message']??'');
    }
    protected function nextCursor(array $cursor,array $items,JobContext $context){return $this->next;}
}
