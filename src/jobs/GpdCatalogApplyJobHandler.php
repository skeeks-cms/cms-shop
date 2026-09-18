<?php
namespace skeeks\cms\shop\jobs;

use skeeks\cms\job\handlers\ChunkedJobHandler;
use skeeks\cms\job\runtime\JobContext;
use skeeks\cms\job\contracts\JobReporterInterface;
use skeeks\cms\job\exceptions\JobCancelledException;
use skeeks\cms\shop\gpd\CatalogApplier;
use skeeks\cms\shop\gpd\CatalogTransport;
use skeeks\cms\shop\gpd\ShopCatalogWriter;

class GpdCatalogApplyJobHandler extends ChunkedJobHandler
{
    public $maxRunSeconds=15;
    private $service; private $settings; private $next;
    protected function loadChunk(JobContext $context,array $cursor)
    {
        if($context->getPayload())throw new \InvalidArgumentException('Expected empty payload.');
        return [$cursor];
    }
    protected function processChunk(array $items,JobContext $context,JobReporterInterface $reporter)
    {
        $checkpoint=static function()use($reporter){$reporter->heartbeat();if($reporter->isCancelled())throw new JobCancelledException('Применение GPD отменено.');};
        if(!$this->settings)$this->settings=\Yii::$app->get('gpd')->forSite((int)$context->getRun()->cms_site_id);
        if(!$this->settings->enabled){$this->next=null;return;}
        if(!$this->service) {
            $site=(int)$context->getRun()->cms_site_id;
            $receiver=\Yii::$app->gpdReceiver->receiverForSite($site);
            $state=$receiver->state();
            $api=\Yii::$app->get(\Yii::$app->gpdReceiver->apiComponent);
            $this->service=new CatalogApplier(\Yii::$app->db,$state['id'],new CatalogTransport($state['source_url'],(string)$api->api_key),new ShopCatalogWriter($site,$this->settings,$api,$checkpoint));
        }
        $saved=json_decode($context->getRun()->result_json??'{}',true)?:[];
        $pendingDetails=$saved['pending_details']??[];
        $stats=$saved['counts']??['created'=>0,'updated'=>0,'deactivated'=>0,'deleted'=>0,'protected'=>0,'kept'=>0,'other_source'=>0,'absent'=>0,'pending'=>0,'unchanged'=>0,'errors'=>0];
        $reporter->setStage('apply','Применяем изменения каталога GPD');
        $page=$this->service->page((int)($items[0]['after']??0));
        $reporter->setTotal((int)$context->getRun()->progress_current+$this->service->remaining((int)($items[0]['after']??0)));
        foreach($page as $entry) {
            $checkpoint();
            try {
                $result=$this->service->apply($entry);$outcome=$result['outcome'];$stats[$outcome]++;
                if(in_array($outcome,['pending','protected','kept','other_source','absent','unchanged'],true))$reporter->countSkipped();else $reporter->countSuccess();
            } catch(JobCancelledException $e){throw $e;}
            catch(\skeeks\cms\shop\gpd\ReferencePendingException $e){$stats['pending']++;$reporter->countSkipped();if(count($pendingDetails)<20)$pendingDetails[]=['product_id'=>$entry['item']['id'],'reason'=>$e->getMessage()];}
            catch(\Throwable $e){$stats['errors']++;$reporter->itemError('gpd_product',$entry['item']['id'],$e instanceof \yii\db\Exception?'Ошибка сохранения товара в БД.':$e->getMessage());}
            $reporter->advance();
            $reporter->setResult(['counts'=>$stats,'remaining'=>$this->service->remaining(),'pending_details'=>$pendingDetails]);
            $reporter->setStage('apply',$this->message($stats));
        }
        $this->next=$page?['after'=>(int)end($page)['state']['product_id']]:null;
        $reporter->setResult(['counts'=>$stats,'remaining'=>$this->service->remaining(),'pending_details'=>$pendingDetails]);
    }
    private function message(array $s): string
    {
        return 'Создано: '.$s['created'].'; обновлено: '.$s['updated'].'; отключено: '.$s['deactivated'].'; удалено: '.$s['deleted'].'; защищено: '.$s['protected'].'; отложено: '.$s['pending'].'; ошибок: '.$s['errors'].'.';
    }
    protected function nextCursor(array $cursor,array $items,JobContext $context){return $this->next;}
    protected function finish(JobContext $context,JobReporterInterface $reporter)
    {
        if(!$this->settings->enabled){$reporter->setStage('disabled','Синхронизация GPD отключена в настройках.');$reporter->countSkipped();return;}
        $result=json_decode($context->getRun()->result_json??'{}',true)?:[];
        $result['remaining']=$this->service->remaining();
        $reporter->setTotal((int)$context->getRun()->progress_current);
        if((int)($result['counts']['pending']??0)>0)$reporter->countWarning((int)$result['counts']['pending']);
        $result['message']=$this->message($result['counts']).' Ожидают применения: '.$result['remaining'].'.';
        $reporter->setResult($result);$reporter->setStage('complete',$result['message']);
    }
}
