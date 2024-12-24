<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\paysystem;

use skeeks\cms\helpers\StringHelper;
use skeeks\cms\models\CmsContractor;
use skeeks\cms\models\CmsContractorBank;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\shop\models\ShopPayment;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\SelectField;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use YooKassa\Client;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class BankTransferPaysystemHandler extends PaysystemHandler
{
    /**
     * @var integer
     */
    public $receiver_contractor_id = '';

    public $receiver_contractor_bank_id = '';

    /**
     * @return array
     */
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name' => "Банковский перевод",
        ]);
    }


    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['receiver_contractor_id'], 'integer'],
            [['receiver_contractor_bank_id'], 'integer'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'receiver_contractor_id'      => "Получатель платежа",
            'receiver_contractor_bank_id' => "Банк получаетеля платежа",
        ]);
    }

    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
        ]);
    }


    /**
     * @return array
     */
    public function getConfigFormFields()
    {
        $cmsContractor = null;
        $bankData = [];
        if ($this->receiver_contractor_id) {
            /**
             * @var $cmsContractor CmsContractor
             */
            $cmsContractor = CmsContractor::findOne($this->receiver_contractor_id);
            $bankData = ArrayHelper::map($cmsContractor->banks, 'id', 'asText');
        }
        return [
            'main' => [
                'class'  => FieldSet::class,
                'name'   => 'Основные',
                'fields' => [
                    'receiver_contractor_id' => [
                        'class'          => SelectField::class,
                        'elementOptions' => [
                            'data' => [
                                'form-reload' => 'true',
                            ],
                        ],
                        'items'          => ArrayHelper::map(CmsContractor::find()->our()->all(), 'id', 'asText'),
                    ],

                    'receiver_contractor_bank_id' => [
                        'class'          => SelectField::class,
                        'items'          => $bankData,
                    ],

                ],
            ],

        ];
    }

    /**
     * @param ShopPayment $shopPayment
     * @return \yii\console\Response|\yii\web\Response
     */
    public function actionPayOrder(ShopOrder $shopOrder)
    {
        $model = $this->getShopBill($shopOrder);

        $yooKassa = $model->shopPaySystem->handler;
        $money = $model->money->convertToCurrency("RUB");
        $returnUrl = $shopOrder->getUrl([], true);

        /**
         * Для чеков нужно указывать информацию о товарах
         * https://yookassa.ru/developers/api?lang=php#create_payment
         */
        $contact_phone = trim($shopOrder->contact_phone);
        $contact_email = trim($shopOrder->contact_email);

        $contact_name = $shopOrder->contact_first_name." ".$shopOrder->contact_last_name;
        $receipt = [];
        if ($yooKassa->is_receipt) {
            if ($this->tax_system_code) {
                $receipt['tax_system_code'] = (int)$this->tax_system_code;
            }

            $receipt['customer'] = [
                'full_name' => trim($contact_name),
            ];

            if ($contact_email) {
                $receipt['customer']['email'] = $contact_email;
            }
            if ($contact_phone) {
                $receipt['customer']['phone'] = $contact_phone;
            }

            foreach ($shopOrder->shopOrderItems as $shopOrderItem) {
                $itemData = [];

                $itemData['description'] = StringHelper::substr($shopOrderItem->name, 0, 128);
                $itemData['quantity'] = (float)$shopOrderItem->quantity;
                $itemData['vat_code'] = 1; //todo: доработать этот момент
                $itemData['payment_mode'] = "full_payment"; //todo: доработать этот момент
                $itemData['payment_subject'] = "commodity"; //todo: доработать этот момент
                $itemData['amount'] = [
                    'value'    => $shopOrderItem->money->amount,
                    'currency' => 'RUB',
                ];

                $receipt['items'][] = $itemData;
            }

            /**
             * Стоимость доставки так же нужно добавить
             */
            if ((float)$shopOrder->moneyDelivery->amount > 0) {
                $itemData = [];
                $itemData['description'] = StringHelper::substr($shopOrder->shopDelivery->name, 0, 128);
                $itemData['quantity'] = 1;
                $itemData['vat_code'] = 1; //todo: доработать этот момент
                $itemData['payment_mode'] = "full_payment"; //todo: доработать этот момент
                $itemData['payment_subject'] = "service"; //todo: доработать этот момент
                $itemData['amount'] = [
                    'value'    => $shopOrder->moneyDelivery->amount,
                    'currency' => 'RUB',
                ];

                $receipt['items'][] = $itemData;
            }

            $totalCalcAmount = 0;
            foreach ($receipt['items'] as $itemData) {
                $totalCalcAmount = $totalCalcAmount + ($itemData['amount']['value'] * $itemData['quantity']);
            }

            $discount = 0;
            if ($totalCalcAmount > (float)$money->amount) {
                $discount = abs((float)$money->amount - $totalCalcAmount);
            }

            /**
             * Стоимость скидки
             */
            //todo: тут можно еще подумать, это временное решение
            if ($discount > 0) {
                $discountValue = $discount;
                foreach ($receipt['items'] as $key => $item) {
                    if ($discountValue == 0) {
                        break;
                    }
                    if ($item['amount']['value']) {
                        if ($item['amount']['value'] >= $discountValue) {
                            $item['amount']['value'] = $item['amount']['value'] - $discountValue;
                            $discountValue = 0;
                        } else {
                            $item['amount']['value'] = 0;
                            $discountValue = $discountValue - $item['amount']['value'];
                        }
                    }

                    $receipt['items'][$key] = $item;
                }

                //$receipt['items'][] = $itemData;
            }
        }


        $client = new Client();
        $client->setAuth($yooKassa->shop_id, $yooKassa->secret_key);
        $payment = $client->createPayment([
            'receipt'      => $receipt,
            'amount'       => [
                'value'    => $money->amount,
                'currency' => 'RUB',
            ],
            'capture'      => true,
            'confirmation' => [
                'type'       => 'redirect',
                'return_url' => $returnUrl,
            ],
            'description'  => 'Заказ №'.$shopOrder->id,
        ],
            uniqid('', true)
        );

        \Yii::info(print_r($payment, true), self::class);

        if (!$payment->id) {
            \Yii::error('Yandex kassa payment id not found', self::class);
            throw new Exception('Yandex kassa payment id not found');
        }

        $model->external_id = $payment->id;
        $model->external_data = [
            'id'           => $payment->id,
            'status'       => $payment->status,
            'created_at'   => $payment->created_at,
            'confirmation' => [
                'type' => $payment->confirmation->type,
                'url'  => $payment->confirmation->getConfirmationUrl(),
            ],
        ];

        if (!$model->save()) {
            \Yii::error("Не удалось сохранить платеж: ".print_r($model->errors, true), self::class);
            throw new Exception("Не удалось сохранить платеж: ".print_r($model->errors, true));
        }

        return \Yii::$app->response->redirect($payment->confirmation->getConfirmationUrl());
    }
}