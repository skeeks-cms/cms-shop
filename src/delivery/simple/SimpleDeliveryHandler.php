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

    public $is_show_user_addresses = true;

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
            [['is_show_user_addresses'], 'boolean'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'is_show_user_addresses' => "Показывать выбор адресов пользователя?",
        ]);
    }

    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
            'is_show_user_addresses' => "При повторном оформлении заказа, клиент может выбрать один из своих адресов",
        ]);
    }


    /**
     * @return array
     */
    public function getConfigFormFields()
    {
        return [

            'is_show_user_addresses' => [
                'class' => BoolField::class,
            ],
        ];
    }

}