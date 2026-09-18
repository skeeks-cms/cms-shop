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

class GpdReferencesJobHandler extends ChunkedJobHandler
{
    public $maxRunSeconds=15;
    private $receiver;private $applier;private $next;
    protected function loadChunk(JobContext $context,array $cursor)
    {
        if($context->getPayload())throw new \InvalidArgumentException('Expected empty payload.');
        return [$cursor];
    }
    protected function processChunk(array $items,JobContext $context,JobReporterInterface $reporter)
    {
        $site=(int)$context->getRun()->cms_site_id;$settings=\Yii::$app->gpd->forSite($site);
        if(!$settings->enabled||!\Yii::$app->gpdReceiver->referencesEnabled){$this->next=null;$reporter->setStage('disabled','Синхронизация справочников отключена.');return;}
        $checkpoint=static function()use($reporter){$reporter->heartbeat();if($reporter->isCancelled())throw new JobCancelledException('Синхронизация справочников отменена.');};
        if(!$this->receiver) {
            $base=\Yii::$app->gpdReceiver->receiverForSite($site)->state();$api=\Yii::$app->get(\Yii::$app->gpdReceiver->apiComponent);
            $transport=new CatalogTransport($base['source_url'],(string)$api->api_key,null,'references');
            $this->receiver=new CatalogReceiver(\Yii::$app->db,$base['id'],$transport,'references');
            $this->receiver->register($site,$base['source_url'],$base['credential_fingerprint']);
            $writer=new ShopReferenceWriter($site,$base['id'],new ShopCatalogWriter($site,$settings,$api,$checkpoint));
            $this->applier=new CatalogApplier(\Yii::$app->db,$base['id'],$transport,$writer,'references');
        }
        $cursor=$items[0];$phase=$cursor['phase']??'receive';
        $result=json_decode($context->getRun()->result_json??'{}',true)?:[];
        $counts=$result['counts']??['received'=>0,'created'=>0,'updated'=>0,'protected'=>0,'kept'=>0,'pending'=>0,'unchanged'=>0,'errors'=>0];
        $checkpoint();
        if($phase==='receive') {
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
                catch(\Throwable $e){++$counts['errors'];$reporter->itemError('gpd_reference',$entry['item']['id'],$e instanceof \yii\db\Exception?'Ошибка сохранения справочника в БД.':$e->getMessage());}
                $reporter->advance();
            }
            $this->next=$page?['phase'=>'apply','after'=>(int)end($page)['state']['product_id']]:null;
        }
        $message='Справочники: получено '.$counts['received'].'; создано '.$counts['created'].'; обновлено '.$counts['updated'].'; защищено '.$counts['protected'].'; ожидают зависимостей '.$counts['pending'].'; ошибок '.$counts['errors'].'.';
        $reporter->setStage($phase,$message);$reporter->setResult(['counts'=>$counts,'remaining'=>$this->applier->remaining(),'message'=>$message]);
    }
    protected function nextCursor(array $cursor,array $items,JobContext $context){return $this->next;}
}
