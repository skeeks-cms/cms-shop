<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\models\behaviors\HasJsonFieldsBehavior;
use skeeks\cms\money\models\MoneyCurrency;
use skeeks\cms\money\Money;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%shop_bill}}".
 *
 * @property int           $id
 * @property int           $created_by
 * @property int           $updated_by
 * @property int           $created_at
 * @property int           $updated_at
 * @property int           $shop_buyer_id Покупатель
 * @property int           $shop_order_id Заказ
 * @property int           $shop_pay_system_id Платежная система
 * @property int           $paid_at Дата оплаты
 * @property int           $shop_payment_id Платеж
 * @property int           $closed_at Дата отмены
 * @property string        $reason_closed Причина отмены
 * @property string        $amount
 * @property string        $currency_code
 * @property string        $description
 * @property string        $code Уникальный код счета
 * @property array         $external_data Внешние данные, например от платежной системы
 *
 * @property MoneyCurrency $currencyCode
 * @property ShopBuyer     $shopBuyer
 * @property ShopOrder     $shopOrder
 * @property ShopPayment   $shopPayment
 * @property ShopPaySystem $shopPaySystem
 *
 * @property string        $url Ссылка на страницу счета
 * @property string        $payUrl Ссылка на страницу оплаты
 *
 * @property Money         $money
 */
class ShopBill extends \skeeks\cms\base\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shop_bill}}';
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            HasJsonFieldsBehavior::class => [
                'class' => HasJsonFieldsBehavior::class,
                'fields' => ['external_data']
            ]
        ]);
    }
    /**
     *
     */
    public function init()
    {
        parent::init();

        $this->on(self::EVENT_AFTER_INSERT, [$this, '_notifyCreate']);
        $this->on(self::EVENT_AFTER_UPDATE, [$this, '_notifyUpdate']);
    }
    /**
     * Уведомление о создании счета на оплату заказчику услуги
     */
    public function _notifyCreate()
    {
        if ($this->shopBuyer && $this->shopBuyer->email) {

            \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail/bill';

            \Yii::$app->mailer->compose('create', [
                'model' => $this,
            ])
                ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName.''])
                ->setTo($this->shopBuyer->email)
                ->setSubject('Выставлен счет №'.$this->id." от ".\Yii::$app->formatter->asDate($this->created_at) . " по заказу №" . $this->shopOrder->id)
                ->send();
        }
    }
    /**
     * Уведомление об оплате счета заказчику услуги
     *
     * @param AfterSaveEvent $event
     */
    public function _notifyUpdate(AfterSaveEvent $event)
    {
        if ($this->shopBuyer && $this->shopBuyer->email) {

            //Если счет стал оплаченым
            if (in_array("paid_at", array_keys($event->changedAttributes)) && $this->paid_at) {

                try {

                    //2 уведомить
                    \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail/bill';

                    \Yii::$app->mailer->compose('paid', [
                        'model' => $this,
                    ])
                        ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName.''])
                        ->setTo($this->shopBuyer->email)
                        ->setSubject('Оплачен счет №'.$this->id." от ".\Yii::$app->formatter->asDate($this->created_at). " по заказу №" . $this->shopOrder->id)
                        ->send();

                } catch (\Exception $e) {
                    \Yii::error($e->getMessage(), self::class);
                }

            }
        }
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'shop_buyer_id', 'shop_order_id', 'shop_pay_system_id', 'paid_at', 'shop_payment_id', 'closed_at'], 'integer'],
            [['shop_buyer_id', 'shop_order_id', 'shop_pay_system_id'], 'required'],
            [['reason_closed', 'description'], 'string'],
            [['external_data'], 'safe'],
            [['amount'], 'number'],
            [['currency_code'], 'string', 'max' => 3],
            [['code'], 'string', 'max' => 255],
            [['code'], 'unique'],
            [['currency_code'], 'exist', 'skipOnError' => true, 'targetClass' => MoneyCurrency::className(), 'targetAttribute' => ['currency_code' => 'code']],
            [['shop_buyer_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopBuyer::className(), 'targetAttribute' => ['shop_buyer_id' => 'id']],
            [['shop_order_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopOrder::className(), 'targetAttribute' => ['shop_order_id' => 'id']],
            [['shop_payment_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopPayment::className(), 'targetAttribute' => ['shop_payment_id' => 'id']],
            [['shop_pay_system_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopPaySystem::className(), 'targetAttribute' => ['shop_pay_system_id' => 'id']],

            [['code'], 'default', 'value' => \Yii::$app->security->generateRandomString()],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id'                 => Yii::t('skeeks/shop/app', 'ID'),
            'shop_buyer_id'      => Yii::t('skeeks/shop/app', 'Покупатель'),
            'shop_order_id'      => Yii::t('skeeks/shop/app', 'Заказ'),
            'shop_pay_system_id' => Yii::t('skeeks/shop/app', 'Платежная система'),
            'paid_at'            => Yii::t('skeeks/shop/app', 'Дата оплаты'),
            'shop_payment_id'    => Yii::t('skeeks/shop/app', 'Платеж'),
            'closed_at'          => Yii::t('skeeks/shop/app', 'Дата отмены'),
            'reason_closed'      => Yii::t('skeeks/shop/app', 'Причина отмены'),
            'amount'             => Yii::t('skeeks/shop/app', 'Amount'),
            'currency_code'      => Yii::t('skeeks/shop/app', 'Currency Code'),
            'description'        => Yii::t('skeeks/shop/app', 'Description'),
            'code'               => Yii::t('skeeks/shop/app', 'Уникальный код счета'),
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrencyCode()
    {
        return $this->hasOne(MoneyCurrency::className(), ['code' => 'currency_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopBuyer()
    {
        return $this->hasOne(ShopBuyer::className(), ['id' => 'shop_buyer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrder()
    {
        return $this->hasOne(ShopOrder::className(), ['id' => 'shop_order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopPayment()
    {
        return $this->hasOne(ShopPayment::className(), ['id' => 'shop_payment_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopPaySystem()
    {
        return $this->hasOne(ShopPaySystem::className(), ['id' => 'shop_pay_system_id']);
    }


    /**
     * @return Money
     */
    public function getMoney()
    {
        return new Money($this->amount, (string)$this->currency_code);
    }

    /**
     * @param bool $scheme
     * @return string
     */
    public function getUrl($scheme = false)
    {
        return Url::to(['/shop/bill', 'code' => $this->code], $scheme);
    }
    /**
     * @param bool $scheme
     * @return string
     */
    public function getPayUrl($scheme = false)
    {
        return Url::to(['/shop/bill/go', 'code' => $this->code], $scheme);
    }
}