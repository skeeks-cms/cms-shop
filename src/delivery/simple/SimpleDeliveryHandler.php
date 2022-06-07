<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\delivery\simple;

use skeeks\cms\shop\delivery\DeliveryHandler;
use skeeks\yii2\form\fields\BoolField;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class SimpleDeliveryHandler extends DeliveryHandler
{

    public $checkoutModelClass = SimpleCheckoutModel::class;
    public $checkoutWidgetClass = SimpleCheckoutWidget::class;

    public $is_check_default = true;

    /**
     * @return array
     */
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name' => "Ввод адреса доставки",
        ]);
    }


    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['is_check_default'], 'integer'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'is_check_default' => "Выбирать первый пункт по умолчанию?",
        ]);
    }

    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
            'is_check_default' => "Если выбрано да, то будет выбран первый пункт выдачи по умолчанию.",
        ]);
    }


    /**
     * @return array
     */
    public function getConfigFormFields()
    {
        return [

            'is_check_default' => [
                'class' => BoolField::class,
            ],
        ];
    }

}