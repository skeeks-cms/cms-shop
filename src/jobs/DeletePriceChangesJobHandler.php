<?php
namespace skeeks\cms\shop\jobs;

use skeeks\cms\shop\services\ScheduledMaintenance;

class DeletePriceChangesJobHandler extends MaintenanceJobHandler
{
    protected function execute(ScheduledMaintenance $service, callable $checkpoint): array
    {
        return $service->deletePriceChanges(30, $checkpoint);
    }
}
