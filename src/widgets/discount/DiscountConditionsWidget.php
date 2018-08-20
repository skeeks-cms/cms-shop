<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\widgets\discount;

use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsContentProperty;
use yii\helpers\Html;
use yii\widgets\InputWidget;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 *
 * @property array availableConditions
 */
class DiscountConditionsWidget extends InputWidget
{
    public static $autoIdPrefix = 'DiscountConditionsWidget';

    /**
     * @var array
     */
    public $clientOptions = [];

    /**
     * @var array
     */
    public $wrapperOptions = [];
    public $allConditions = [];
    public function run()
    {
        $this->clientOptions['id'] = $this->id;
        $this->wrapperOptions['id'] = $this->id;

        Html::addCssClass($this->wrapperOptions, 'sx-discount-conditions');

        $element = Html::activeTextarea($this->model, $this->attribute, $this->options);


        return $this->render('discount-conditions', [
            'element' => $element,
        ]);
    }
    /**
     * @return array
     */
    public function getAvailableConditions()
    {
        $element = new CmsContentElement();

        $fields = [];

        $fields[] = " - ";
        $fields['group'] = "Группа условий";

        $elementOptions = [];
        foreach ($element->attributeLabels() as $key => $name) {
            if (in_array($key, [
                'tree_id',
                'treeIds',
                'id',
                'created_by',
                'name',
            ])) {
                $elementOptions['element.'.$key] = $name;
                $this->allConditions['element.'.$key] = $name;
            }
        }

        $fields["Основные свойства"] = $elementOptions;

        $props = CmsContentProperty::find()->all();
        $propsOptions = [];
        /**
         * @var $props CmsContentProperty[]
         */
        if ($props) {
            foreach ($props as $prop) {
                $propsOptions['rp.'.$prop->code] = $prop->name;
                $this->allConditions['rp.'.$prop->code] = $prop->name;
            }
        }

        $fields["Свойства"] = $propsOptions;

        return $fields;
    }
}