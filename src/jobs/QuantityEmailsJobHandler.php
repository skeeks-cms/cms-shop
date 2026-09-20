<?php
namespace skeeks\cms\shop\jobs;
use skeeks\cms\shop\services\ScheduledMaintenance;
class QuantityEmailsJobHandler extends MaintenanceJobHandler
{
    protected function execute(ScheduledMaintenance $service, callable $checkpoint): array
    {
        return $service->quantityEmails($checkpoint);
    }
}