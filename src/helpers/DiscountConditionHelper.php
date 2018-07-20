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
        'data' => $conditions,
        'shopCmsContentElement' => $shopCmsContentElement,
    ]);

    return $condition->isTrue;
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
        if ($this->type == 'group') {

            if (!$this->rules) {
                return true;
            }

            if ($this->rules_type == 'and' && $this->condition == 'equal') {

                foreach ($this->rules as $rule) {
                    if (!$rule->getIsTrue()) {
                        /*print_r($rule);die;*/
                        return false;
                    }
                }

                return true;

            } elseif ($this->rules_type == 'or' && $this->condition == 'equal') {

                foreach ($this->rules as $rule) {
                    if ($rule->getIsTrue()) {
                        return true;
                    }
                }

                return false;
            } /*elseif ($this->rules_type == 'and' && $this->condition == 'not_equal') {

                foreach ($this->rules as $rule)
                {
                    if (!$rule->getIsTrue()) {
                        return false;
                    }
                }

                return false;
            }*/

        } else {

            if ($this->field && strpos("element.", $this->field) != -1) {
                $field = str_replace("element.", "", $this->field);

                return $this->isTrue($this->value, $this->shopCmsContentElement->{$field});

                if (isset($this->shopCmsContentElement->{$field})) {


                    if (is_array($this->value)) {

                    } else {
                        if (is_array($this->shopCmsContentElement->{$field})) {
                            if (in_array((int)$this->value, $this->shopCmsContentElement->{$field})) {
                                return true;
                            } else {
                                return false;
                            }
                        } else {
                            if ($this->shopCmsContentElement->{$field} == $this->value) {
                                return true;
                            } else {
                                return false;
                            }
                        }
                    }
                }
            }

            return true;
        }

        return true;
    }

    public function isTrue($value, $fieldValue)
    {
        if (is_array($value)) {

            $result = false;

            foreach ($value as $val)
            {
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