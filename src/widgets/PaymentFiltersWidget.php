<?php

namespace skeeks\cms\shop\widgets;

use skeeks\cms\backend\widgets\FiltersWidget;
use skeeks\cms\rbac\CmsManager;

/** Keep the privileged employee selector available in older saved representations. */
class PaymentFiltersWidget extends FiltersWidget
{
    public function init()
    {
        if (!isset($this->configBehaviorData['configClassName'])) {
            // Saved representations created before this subclass use FiltersWidget.
            $this->configBehaviorData['configClassName'] = FiltersWidget::class;
            $behavior = $this->configBehavior;
            if (!$behavior->configStorage->fetch($behavior)) {
                // Preserve representations saved while the subclass used its own key.
                $behavior->configClassName = self::class;
                if (!$behavior->configStorage->fetch($behavior)) {
                    $behavior->configClassName = FiltersWidget::class;
                }
            }
            $this->configBehaviorData['configClassName'] = $behavior->configClassName;
        }

        parent::init();
    }

    protected function _applyFilters()
    {
        $this->visibleFilters = array_values(array_diff((array)$this->visibleFilters, ['available_for_user_id']));
        if (\Yii::$app->user->can(CmsManager::PERMISSION_ROLE_ADMIN_ACCESS)) {
            $this->visibleFilters[] = 'available_for_user_id';
        }
        return parent::_applyFilters();
    }
}
