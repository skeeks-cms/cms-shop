<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\helpers;

use skeeks\cms\shop\models\ShopCmsContentElement;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 *
 * Условия применения скидки
 *
 * $condition = new DiscountConditionHelper([
 * 'data' => $conditions,
 * 'shopCmsContentElement' => $shopCmsContentElement,
 * ]);
 *
 * return $condition->isTrue;
 *
 *
 * @property bool isTrue
 */
class DiscountConditionHelper extends Component
{
    public $data = [];

    public $type;
    public $condition;
    public $rules_type;
    public $rules;
    public $field;
    public $value;

    /**
     * @var ShopCmsContentElement
     */
    public $shopCmsContentElement;

    /**
     *
     */
    public function init()
    {
        $rulesTmp = [];

        if ($this->data) {
            $this->type = ArrayHelper::getValue($this->data, 'type', 'group');
            $this->condition = ArrayHelper::getValue($this->data, 'condition', 'equal');

            if ($this->type == 'group') {
                $this->rules_type = ArrayHelper::getValue($this->data, 'rules_type', 'and');
            } else {
                $this->value = ArrayHelper::getValue($this->data, 'value');
                $this->field = ArrayHelper::getValue($this->data, 'field');
            }

            if ($rules = ArrayHelper::getValue($this->data, 'rules')) {
                foreach ($rules as $ruledata) {
                    $rulesTmp[] = new self([
                        'data'                  => $ruledata,
                        'shopCmsContentElement' => $this->shopCmsContentElement,
                    ]);
                }
            }
        }

        $this->rules = $rulesTmp;
    }

    public function getIsTrue()
    {
        $result = true;

        if ($this->type == 'group') {
            if ($this->rules) {
                if ($this->rules_type == 'or') {
                    $result = false;
                    foreach ($this->rules as $rule) {
                        if ($rule->getIsTrue()) {
                            $result = true;
                            break;
                        }
                    }
                } else {
                    foreach ($this->rules as $rule) {
                        if (!$rule->getIsTrue()) {
                            $result = false;
                            break;
                        }
                    }
                }
            }
        } else {
            if ($this->field && strpos($this->field, "element.") !== false) {
                $field = str_replace("element.", "", $this->field);
                $result = $this->isTrue($this->value, $this->shopCmsContentElement->{$field});
            } elseif ($this->field && strpos($this->field, "shop.") !== false) {
                $field = str_replace("shop.", "", $this->field);
                $result = $this->isTrue($this->value, $this->shopCmsContentElement->shopProduct->{$field});
            }
        }

        return in_array($this->condition, ['not_equal', 'or'], true) ? !$result : $result;
    }

    public function isTrue($value, $fieldValue)
    {
        if (is_array($value)) {

            $result = false;

            foreach ($value as $val) {
                if (is_array($fieldValue)) {
                    if (in_array((int)$val, $fieldValue)) {
                        return true;
                    }
                } else {
                    if ($val == $fieldValue) {
                        return true;
                    }
                }
            }

            return $result;

        } else {
            if (is_array($fieldValue)) {
                if (in_array((int)$value, $fieldValue)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                if ($value == $fieldValue) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        return true;
    }
}
