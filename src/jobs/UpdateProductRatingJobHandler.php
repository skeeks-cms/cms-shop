<?php
namespace skeeks\cms\shop\jobs;

use skeeks\cms\shop\services\ScheduledMaintenance;

class UpdateProductRatingJobHandler extends MaintenanceJobHandler
{
    protected function execute(ScheduledMaintenance $service, callable $checkpoint): array
    {
        return $service->updateProductRating($checkpoint);
    }
}
