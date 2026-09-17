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
    protected function loadChunk(JobContext $context,array $cursor)
    {
        $payload=$context->getPayload();
        if (array_keys($payload)!==['connection'] || !is_string($payload['connection'])) {
            throw new \InvalidArgumentException('Expected only connection ID.');
        }
        if (!$this->receiver) $this->receiver=\Yii::$app->get('gpdReceiver')->receiver($payload['connection'],(int)$context->getRun()->cms_site_id);
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
        $progress=(int)$context->getRun()->progress_current;
        $reporter->advance($result['count']);
        $reporter->countSuccess($result['count']);
        $summary=$this->receiver->summary();
        // Persisted bootstrap counters remain meaningful across process restarts.
        $reporter->setTotal($summary['phase']==='snapshot'?$progress+$result['count']+$summary['snapshot_total']-$summary['snapshot_received']:null);
        $reporter->setResult($summary);
    }
    protected function nextCursor(array $cursor,array $items,JobContext $context) {return $this->next;}
    protected function finish(JobContext $context,JobReporterInterface $reporter)
    {
        $result=$this->receiver->summary();
        $reporter->setResult($result);
        if ($result['unresolved']) {

            $reporter->itemError('gpd_connection',$context->get('connection'),'Не все состояния опубликованы API; повторная проверка — в следующем запуске.');
        }
    }
}
