<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\models\behaviors\Implode;
use skeeks\cms\models\Core;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_order_status}}".
 *
 * @property string            $name
 * @property string|null       $description
 * @property integer           $priority
 * @property string|null       $color
 * @property string|null       $bg_color
 * @property string|null       $btn_name
 * @property string|null       $email_notify_description
 * @property string|null       $order_page_description
 * @property integer           $is_comment_required
 * @property array|null        $client_available_statuses
 * @property integer|null      $auto_next_shop_order_status_id
 * @property integer|null      $auto_next_status_time
 * @property integer|null      $is_payment_allowed
 * @property integer|null      $is_install_after_pay
 *
 * @property bool              $isFinished Финальный статус заказа? Когда покупатель уже получил заказ
 * @property string            $btnName
 * @property ShopOrder[]       $shopOrders
 * @property ShopOrder         $autoNextShopOrderStatus
 * @property ShopOrderStatus[] $clientAvailbaleStatuses
 */
class ShopOrderStatus extends Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_order_status}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            Implode::class => [
                'class'  => Implode::class,
                'fields' => [
                    'client_available_statuses',
                ],
            ],
        ]);
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'name' => \Yii::t('skeeks/shop/app', 'Name'),

            'description' => \Yii::t('skeeks/shop/app', 'Описание'),
            'priority'    => \Yii::t('skeeks/shop/app', 'Priority'),

            'color'    => \Yii::t('skeeks/shop/app', 'Цвет названия статуса'),
            'bg_color' => \Yii::t('skeeks/shop/app', 'Цвет фона статуса'),

            'email_notify_description' => \Yii::t('skeeks/shop/app', 'Дополнительный текст email уведомления'),
            'order_page_description'   => \Yii::t('skeeks/shop/app', 'Дополнительный текст на странице заказа'),

            'is_comment_required'       => \Yii::t('skeeks/shop/app', 'Комментарий к статусу обязателен?'),
            'client_available_statuses' => \Yii::t('skeeks/shop/app', 'Доступные статусы для клиента'),

            'btn_name' => \Yii::t('skeeks/shop/app', 'Название на кнопке смены статуса'),

            'auto_next_shop_order_status_id' => \Yii::t('skeeks/shop/app', 'Автоматически изменить этот статус на'),
            'auto_next_status_time'          => \Yii::t('skeeks/shop/app', 'Статус будет изменен автоматически через'),

            'is_payment_allowed'   => \Yii::t('skeeks/shop/app', 'Разрешить онлайн оплату?'),
            'is_install_after_pay' => \Yii::t('skeeks/shop/app', 'Установить этот статус после оплаты?'),
        ]);
    }
    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return array_merge(parent::attributeLabels(), [
            'description' => \Yii::t('skeeks/shop/app', 'Короткая расшфировка статуса заказа'),

            'email_notify_description' => \Yii::t('skeeks/shop/app', 'Этот текст получают клиенты в email уведомлении. Допустимо использование {order_url} {order_id}'),
            'order_page_description'   => \Yii::t('skeeks/shop/app', 'Этот текст отображается клиенту на странице с заказом, в случае этого статуса'),

            'is_comment_required'       => \Yii::t('skeeks/shop/app', 'Если эта опция выбрана, то при установке этого статуса у заказа, потребуется ОБЯЗАТЕЛЬНО написать комментарий!'),
            'client_available_statuses' => \Yii::t('skeeks/shop/app', 'Когда заказ находится в этом статусе, то клиенту доступны кнопки для смены статуса выбранные в этом поле.'),

            'btn_name' => \Yii::t('skeeks/shop/app', 'Клиент увидит название на кнопке при смене сатуса на этот. Например для смены статуса "отменен" название на кнопке должно быть "отменить"'),

            'auto_next_shop_order_status_id' => \Yii::t('skeeks/shop/app', 'Текущий статус будет изменен автоматически на новый, который выбран в этом поле.'),
            'auto_next_status_time'          => \Yii::t('skeeks/shop/app', 'Статус будет изменен через указанное количество сек.'),

            'is_payment_allowed'   => \Yii::t('skeeks/shop/app', 'Разрешить онлайн оплату заказа если заказ находится в этом статусе?'),
            'is_install_after_pay' => \Yii::t('skeeks/shop/app', 'После онлайн оплаты, статус заказа клиента станет этим.'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['priority'], 'integer'],
            [['is_comment_required'], 'integer'],
            [['auto_next_shop_order_status_id'], 'integer'],
            [['auto_next_status_time'], 'integer'],
            [['client_available_statuses'], 'safe'],
            [['name'], 'required'],
            [['description'], 'string', 'max' => 255],
            [['order_page_description'], 'string'],
            [['email_notify_description'], 'string'],
            [['btn_name'], 'string'],

            [['name'], 'string', 'max' => 255],

            [['color'], 'string', 'max' => 32],
            [['bg_color'], 'string', 'max' => 32],
            [['is_payment_allowed'], 'integer'],
            [['is_install_after_pay'], 'integer'],


            [['description', 'color', 'bg_color', 'order_page_description', 'email_notify_description'], 'default', 'value' => null],

            [['is_install_after_pay'], 'default', 'value' => null],
            [
                ['is_install_after_pay'],
                function () {
                    if ($this->is_install_after_pay == 0 || $this->is_install_after_pay == "0") {
                        $this->is_install_after_pay = null;
                    }
                },
            ],

            [['is_install_after_pay'], 'unique'],
        ]);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrders()
    {
        return $this->hasMany(ShopOrder::class, ['shop_order_status_id' => 'id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAutoNextShopOrderStatus()
    {
        return $this->hasOne(ShopOrderStatus::class, ['id' => 'auto_next_shop_order_status_id']);
    }

    /**
     * @return array|\skeeks\cms\query\CmsActiveQuery
     */
    public function getClientAvailbaleStatuses()
    {
        if (!$this->client_available_statuses) {
            return [];
        }

        $q = self::find()->andWhere(['id' => $this->client_available_statuses]);
        $q->multiple = true;

        return $q;
    }

    /**
     * @return string
     */
    public function getBtnName()
    {
        if ($this->btn_name) {
            return $this->btn_name;
        }

        return $this->name;
    }

    /**
     * @param ShopOrder $order
     * @return mixed|string|null
     */
    public function getEmailNotifyDescriptionFormated(ShopOrder $order)
    {
        $result = $this->email_notify_description;

        $result = str_replace("{order_url}", $order->url, $result);
        $result = str_replace("{order_id}", $order->url, $result);

        return $result;
    }

    /**
     * Финальный статус заказа?
     * @return bool
     */
    public function getIsFinished()
    {
        $last = self::find()->orderBy(['priority' => SORT_DESC])->limit(1)->one();
        if ($this->id == $last->id) {
            return true;
        }

        return false;
    }
}