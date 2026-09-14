<?php
namespace skeeks\cms\shop\jobs;

use skeeks\cms\job\contracts\JobReporterInterface;
use skeeks\cms\job\exceptions\JobCancelledException;
use skeeks\cms\job\handlers\AbstractJobHandler;
use skeeks\cms\job\runtime\JobContext;
use skeeks\cms\shop\services\ScheduledMaintenance;

abstract class MaintenanceJobHandler extends AbstractJobHandler
{
    abstract protected function execute(ScheduledMaintenance $service, callable $checkpoint): array;

    public function run(JobContext $context, JobReporterInterface $reporter): void
    {
        $reporter->setTotal(null);
        $processed = 0;
        $stage = null;
        $checkpoint = static function (array $progress) use ($reporter, &$processed, &$stage) {
            if ($reporter->isCancelled()) { throw new JobCancelledException('Maintenance cancelled.'); }
            if ($stage !== $progress['stage']) {
                $stage = $progress['stage'];
                $reporter->setStage($stage);
            }
            $delta = $progress['processed'] - $processed;
            if ($delta > 0) { $reporter->advance($delta); $reporter->countSuccess($delta); }
            $processed = $progress['processed'];
            $reporter->heartbeat();
        };
        $checkpoint(['stage' => 'starting', 'processed' => 0]);
        $result = $this->execute(\Yii::createObject(ScheduledMaintenance::class), $checkpoint);
        $reporter->setResult($result);
        if (($result['enabled'] ?? true) === false) { $reporter->countSkipped(); }
        $reporter->setStage('completed');
    }
}
