<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\delivery\simple;

use skeeks\cms\models\CmsUserAddress;
use skeeks\cms\money\Money;
use skeeks\cms\shop\delivery\DeliveryCheckoutModel;
use skeeks\cms\shop\models\ShopOrder;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

/**
 *
 * @property Money $money
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class SimpleCheckoutModel extends DeliveryCheckoutModel
{
    /**
     * @var string
     */
    public $id;

    public $cms_user_address_id;

    public $address;
    public $latitude;
    public $longitude;
    public $entrance;
    public $floor;
    public $comment;
    public $apartment_number;

    public $price;


    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [
                ['address'],
                'required',
                'message' => 'Заполните адрес доставки',
                'when'    => function () {
                    return !$this->cms_user_address_id;
                },
            ],

            [['cms_user_address_id'], 'integer'],

            [['comment'], 'string'],
            [['address'], 'string'],
            [['latitude', 'longitude'], 'number'],
            [
                [
                    'floor',
                    'apartment_number',
                    'entrance',
                ],
                'string',
            ],

            [['price'], 'number'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'address'          => "Адрес",
            'latitude'         => "Широта",
            'longitude'        => "Долгота",
            'entrance'         => "Подъезд",
            'floor'            => "Этаж",
            'apartment_number' => "Номер квартиры",
            'comment'          => "Комментарий к адресу, например код домофона и прочие особенности",
        ]);
    }

    /**
     * @return Money
     */
    public function getMoney()
    {
        if ((float)$this->price) {
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
        //Чистка данных по пункту вывоза
        $order->shop_store_id = null;

        $order->delivery_address = $this->address;
        $order->delivery_latitude = $this->latitude;
        $order->delivery_longitude = $this->longitude;
        $order->delivery_entrance = $this->entrance;
        $order->delivery_apartment_number = $this->apartment_number;
        $order->delivery_floor = $this->floor;
        $order->delivery_comment = $this->comment;

        if ($this->cms_user_address_id) {
            $order->cms_user_address_id = $this->cms_user_address_id;
        } else {
            if ($order->cmsUser) {
                if (!$order->cmsUser->getCmsUserAddresses()->andWhere(['value' => trim($this->address)])->exists()) {
                    $cmsUserAddress = new CmsUserAddress();
                    $cmsUserAddress->cms_user_id = $order->cms_user_id;
                    $cmsUserAddress->value = $this->address;
                    $cmsUserAddress->latitude = $this->latitude;
                    $cmsUserAddress->longitude = $this->longitude;
                    $cmsUserAddress->entrance = $this->entrance;
                    $cmsUserAddress->floor = $this->floor;
                    $cmsUserAddress->comment = $this->comment;
                    $cmsUserAddress->apartment_number = $this->apartment_number;
                    if (!$cmsUserAddress->save()) {
                        throw new Exception(print_r($cmsUserAddress->errors, true));
                    }
                }
            }
            
        }

        return $this;
    }


    /**
     * @return array
     */
    public function getVisibleAttributes()
    {
        $result = [];

        if ($this->address) {
            $result['address'] = [
                'value' => $this->address,
                'label' => 'Адрес',
            ];
        }
        if ($this->entrance) {
            $result['entrance'] = [
                'value' => $this->entrance,
                'label' => 'Подъезд',
            ];
        }
        if ($this->floor) {
            $result['floor'] = [
                'value' => $this->floor,
                'label' => 'Этаж',
            ];
        }
        if ($this->apartment_number) {
            $result['apartment_number'] = [
                'value' => $this->apartment_number,
                'label' => 'Номер квартиры',
            ];
        }
        if ($this->comment) {
            $result['comment'] = [
                'value' => $this->comment,
                'label' => 'Комментарий к адресу',
            ];
        }

        return $result;
    }


}