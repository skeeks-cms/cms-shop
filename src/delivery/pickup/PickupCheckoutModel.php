<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\delivery\pickup;

use skeeks\cms\money\Money;
use skeeks\cms\shop\delivery\DeliveryCheckoutModel;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\shop\models\ShopStore;
use skeeks\yii2\config\DynamicConfigModel;
use yii\helpers\ArrayHelper;

/**
 *
 * @property Money $money
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class PickupCheckoutModel extends DeliveryCheckoutModel
{
    /**
     * @var string
     */
    public $id;

    public $shop_store_id;
    public $price;


    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['shop_store_id'], 'required', 'message' => 'Выберите пункт выдачи заказа.'],
            [['shop_store_id'], 'integer'],
            [['price'], 'number'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'shop_store_id' => "Пункты выдачи",
        ]);
    }

    /**
     * @return Money
     */
    public function getMoney()
    {
        if ($this->price) {
            return new Money((string)$this->price, $this->shopOrder->currency_code);
        }
        return parent::getMoney();
    }

    
    /**
     * Установить необходимые данные по заказу
     * Вызывается перед сохранением заказа
     *
     * @return $this
     */
    public function modifyOrder(ShopOrder $order)
    {
        $order->shop_store_id = $this->shop_store_id;

        //Чистка данных по доставке
        $order->cms_user_address_id = null;
        $order->delivery_address = "";
        $order->delivery_apartment_number = "";
        $order->delivery_floor = "";
        $order->delivery_entrance = "";
        $order->delivery_latitude = "";
        $order->delivery_longitude = "";
        $order->delivery_comment = "";

        return $this;
    }


    /**
     * @return array
     */
    public function getVisibleAttributes()
    {
        $shop_store_id = $this->shop_store_id;
        if (!$this->shop_store_id) {
            $shop_store_id = $this->shopOrder->shop_store_id;
        }
        $shopStore = ShopStore::findOne($shop_store_id);

        $result = [];

        if ($shopStore) {
            $result['shop_store_id'] = [
                'value' => $shopStore ? $shopStore->address : "Пункт удален",
                'label' => 'Пункт выдачи'
            ];
        }



        return $result;
    }




}