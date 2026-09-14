<?php
namespace skeeks\cms\shop\jobs;

use skeeks\cms\shop\services\ScheduledMaintenance;

class DeleteEmptyCartsJobHandler extends MaintenanceJobHandler
{
    protected function execute(ScheduledMaintenance $service, callable $checkpoint): array
    {
        return $service->deleteEmptyCarts(3, $checkpoint);
    }
}
