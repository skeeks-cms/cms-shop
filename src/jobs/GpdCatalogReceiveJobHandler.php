<?php
namespace skeeks\cms\shop\jobs;

use skeeks\cms\job\handlers\ChunkedJobHandler;
use skeeks\cms\job\runtime\JobContext;
use skeeks\cms\job\contracts\JobReporterInterface;

/** Receives metadata only; never reports receipt as application to the shop. */
class GpdCatalogReceiveJobHandler extends ChunkedJobHandler
{
    public $maxRunSeconds=15;
    private $receiver;
    private $next;
    private $disabled=false;
    private $applyRequested=false;
    protected function loadChunk(JobContext $context,array $cursor)
    {
        if (\Yii::$app->has('gpd') && !\Yii::$app->get('gpd')->forSite((int)$context->getRun()->cms_site_id)->enabled) {$this->disabled=true;return [];}
        $payload=$context->getPayload();
        if ($payload && (array_keys($payload)!==['connection'] || !is_string($payload['connection']))) {
            throw new \InvalidArgumentException('Expected empty payload.');
        }
        if (!$this->receiver) $this->receiver=\Yii::$app->get('gpdReceiver')->receiverForSite((int)$context->getRun()->cms_site_id,$payload['connection']??null);
        return [$cursor];
    }
    protected function processChunk(array $items,JobContext $context,JobReporterInterface $reporter)
    {
        $cursor=$items[0];
        if (($cursor['phase']??'receive')==='resolve') {
            $reporter->setStage('resolve','Проверяем отложенные состояния товаров');
            $result=$this->receiver->resolve((int)($cursor['after']??0));
            $this->next=$result['more']?['phase'=>'resolve','after'=>$result['after']]:null;
        } else {
            $result=$this->receiver->receive();
            $reporter->setStage($result['stage'],'Получение журнала GPD — без изменения товаров сайта');
            $this->next=$result['more']?['phase'=>'receive']:['phase'=>'resolve','after'=>0];
        }
        if(!$this->applyRequested && $result['count']>0) {
            \skeeks\cms\shop\gpd\CatalogApplyDispatch::forSite((int)$context->getRun()->cms_site_id);
            $this->applyRequested=true;
        }
        $progress=(int)$context->getRun()->progress_current;
        $reporter->advance($result['count']);
        $reporter->countSuccess($result['count']);
        $summary=$this->receiver->summary();
        $saved=json_decode($context->getRun()->result_json??'{}',true)?:[];
        $stats=$saved['run']??['cards_received'=>0,'exclusions_received'=>0,'pending_received'=>0,'rechecked'=>0];
        if (($cursor['phase']??'receive')==='resolve') $stats['rechecked']+=$result['count'];
        else {
            $stats['cards_received']+=(int)($result['operations']['upsert']??0);
            $stats['exclusions_received']+=(int)($result['operations']['revoke']??0);
            $stats['pending_received']+=(int)($result['operations']['pending']??0);
        }
        $summary['run']=$stats;
        // Persisted bootstrap counters remain meaningful across process restarts.
        $reporter->setTotal($summary['phase']==='snapshot'?$progress+$result['count']+$summary['snapshot_total']-$summary['snapshot_received']:null);
        $reporter->setResult($summary);
    }
    protected function nextCursor(array $cursor,array $items,JobContext $context) {return $this->next;}
    protected function finish(JobContext $context,JobReporterInterface $reporter)
    {
        if($this->disabled){$reporter->setStage('disabled','Синхронизация GPD отключена в настройках.');$reporter->countSkipped();return;}
        \skeeks\cms\shop\gpd\CatalogApplyDispatch::forSite((int)$context->getRun()->cms_site_id);
        $result=$this->receiver->summary();
        $saved=json_decode($context->getRun()->result_json??'{}',true)?:[];
        $stats=$saved['run']??['cards_received'=>0,'exclusions_received'=>0,'pending_received'=>0,'rechecked'=>0];
        $result['run']=$stats;
        $message=($stats['cards_received'] || $stats['exclusions_received'] || $stats['pending_received'])
            ? 'Получено: карточек '.$stats['cards_received'].'; исключений '.$stats['exclusions_received'].'; ожидают данных '.$stats['pending_received'].'.'
            : 'Новых изменений нет.';
        if($stats['rechecked']) $message.=' Перепроверено: '.$stats['rechecked'].'.';
        $message.=' Требуют проверки: '.$result['unresolved'].'. Товары сайта не изменялись.';
        $result['message']=$message;
        $reporter->setResult($result);
        $reporter->setStage('complete',$message);
        if ($result['unresolved']) {
            $reporter->itemError('gpd_connection',$context->get('connection'),'Не все состояния опубликованы API; повторная проверка — в следующем запуске.');
        }
    }
}