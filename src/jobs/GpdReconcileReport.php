<?php
namespace skeeks\cms\shop\jobs;

use skeeks\cms\job\models\CmsJobRun;
use skeeks\cms\job\reports\JobRunReport;

/** Stage table of the GPD reconcile run above the standard cms-job card. */
class GpdReconcileReport extends JobRunReport
{
    public $view='@skeeks/cms/shop/views/job/gpd-reconcile';

    public function details(CmsJobRun $run): array
    {
        return GpdReconcileProgress::collect($run);
    }
}
