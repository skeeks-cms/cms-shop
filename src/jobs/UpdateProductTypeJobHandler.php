<?php
namespace skeeks\cms\shop\jobs;

use skeeks\cms\shop\services\ScheduledMaintenance;

class UpdateProductTypeJobHandler extends MaintenanceJobHandler
{
    protected function execute(ScheduledMaintenance $service, callable $checkpoint): array
    {
        return $service->updateProductType($checkpoint);
    }
}
