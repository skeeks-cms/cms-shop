<?php

namespace skeeks\cms\shop\widgets;

use skeeks\cms\backend\widgets\FiltersWidget;
use skeeks\cms\rbac\CmsManager;

/** Keep the privileged employee selector available in older saved representations. */
class PaymentFiltersWidget extends FiltersWidget
{
    protected function _applyFilters()
    {
        $this->visibleFilters = array_values(array_diff((array)$this->visibleFilters, ['available_for_user_id']));
        if (\Yii::$app->user->can(CmsManager::PERMISSION_ROLE_ADMIN_ACCESS)) {
            $this->visibleFilters[] = 'available_for_user_id';
        }
        return parent::_applyFilters();
    }
}
