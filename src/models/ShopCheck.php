<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\helpers\StringHelper;
use skeeks\cms\models\behaviors\HasJsonFieldsBehavior;
use skeeks\cms\models\CmsSite;
use skeeks\cms\models\CmsUser;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "shop_check".
 *
 * @property int               $id
 * @property int|null          $created_at
 * @property int|null          $updated_at
 * @property int|null          $created_by
 * @property int|null          $updated_by
 * @property int               $cms_site_id
 * @property string|null       $status Статус чека (wait, approved, error)
 * @property string            $doc_type Тип документа (sale, return, buy, bure_return)
 * @property int|null          $shop_store_id Магазин
 * @property int|null          $shop_cashebox_id Касса
 * @property int|null          $shop_cashebox_shift_id Смена
 * @property int|null          $shop_order_id Заказ
 * @property int|null          $cms_user_id Клиент
 * @property string            $email Email или телефон клиента
 * @property string|null       $cashier_name Кассир
 * @property string|null       $cashier_position Должность
 * @property int|null          $cashier_cms_user_id Кассир - пользователь
 * @property string|null       $tax_mode Применяемая система налогообложения
 * @property float             $amount Сумма чека
 * @property array             $moneyPositions Json объект с типом оплаты и суммы платежа
 * @property array             $inventPositions Json объект с позициями
 * @property int|null          $fiscal_date_at
 * @property string|null       $fiscal_date
 * @property string|null       $fiscal_kkt_number
 * @property string|null       $fiscal_fn_number
 * @property string|null       $fiscal_fn_doc_number
 * @property string|null       $fiscal_fn_doc_mark
 * @property string|null       $fiscal_shift_number
 * @property string|null       $fiscal_check_number
 * @property string|null       $fiscal_ecr_registration_umber
 * @property string|null       $qr QR код чека
 * @property string|null       $error_message Сообщение об ошибке
 * @property int               $is_print Печатать бумажную версию чека?
 * @property string|null       $seller_address    Адрес торговой точки
 * @property string|null       $seller_name Название юр. лица (ИП или ООО и т.д.)
 * @property string|null       $seller_inn ИНН торговой точки
 * @property string|null       $kkm_payments_address Платежные адреса (сайт; Разъездная; Магазин)
 *
 *
 * @property string|null       $provider_uid Уникальный ID внешней системы
 * @property string|null       $provider_request_data Данные для создания объекта чека
 * @property string|null       $provider_response_data Все данные из внешней системы по чеку
 *
 * @property string            $taxModeAsText
 * @property string            $statusAsText
 * @property string            $docTypeAsText
 * @property boolean           $isApproved Проведен/фискализирован?
 *
 * @property CmsUser           $cashierCmsUser
 * @property CmsSite           $cmsSite
 * @property CmsUser           $cmsUser
 * @property ShopCashebox      $shopCashebox
 * @property ShopCasheboxShift $shopCasheboxShift
 * @property ShopOrder         $shopOrder
 * @property ShopStore         $shopStore
 */
class ShopCheck extends \skeeks\cms\base\ActiveRecord
{
    const STATUS_NEW = "new"; //Новый
    const STATUS_WAIT = "wait"; //Новый
    const STATUS_APPROVED = "approved"; //Фискализирован и проведен
    const STATUS_ERROR = "error"; //какие то ошибки

    const DOCTYPE_SALE = "sale"; //приход
    const DOCTYPE_RETURN = "return"; //возврат прихода
    const DOCTYPE_BUY = "buy"; //расход
    const DOCTYPE_BUY_RETURN = "buy_return"; //возврат расхода

    const TAXMODE_COMMON = "common"; //ОСН
    const TAXMODE_SIMPLIFIED = "simplified"; //УСН Доходы
    const TAXMODE_SIMPLIFIED_WITH_EXPENSE = "simplified_with_expense"; //УСН Доход минус расход
    const TAXMODE_ENVD = "envd"; //ЕНВД
    const TAXMODE_PATENT = "patent"; //ПСН
    const TAXMODE_COMMON_AGRICULTURAL = "common_agricultural"; //ЕСХН


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shop_check}}';
    }

    public function init()
    {
        $this->on(self::EVENT_AFTER_FIND, function () {
            $this->amount = (float)$this->amount;
        });
        return parent::init();
    }

    /**
     * @return string
     */
    public function getStatusAsText()
    {
        return (string)ArrayHelper::getValue(static::statuses(), $this->status, "-");
    }
    /**
     * @return string
     */
    public function getDocTypeAsText()
    {
        return (string)ArrayHelper::getValue(static::docTypes(), $this->doc_type, "-");
    }
    /**
     * @return string
     */
    public function getTaxModeAsText()
    {
        return (string)ArrayHelper::getValue(static::taxModes(), $this->tax_mode, "-");
    }

    /**
     * @return string[]
     */
    static public function statuses()
    {
        return [
            static::STATUS_NEW      => "Новый",
            static::STATUS_WAIT     => "Ожидает фискализации",
            static::STATUS_APPROVED => "Проведен/Фискализирован",
            static::STATUS_ERROR    => "Ошибка фискализации",
        ];
    }

    /**
     * @return string[]
     */
    static public function docTypes()
    {
        return [
            static::DOCTYPE_SALE       => "Приход",
            static::DOCTYPE_RETURN     => "Возврат прихода",
            static::DOCTYPE_BUY        => "Расход",
            static::DOCTYPE_BUY_RETURN => "Возврат расхода",
        ];
    }

    /**
     * @return string[]
     */
    static public function taxModes()
    {
        return [
            static::TAXMODE_COMMON                  => "ОСН",
            static::TAXMODE_SIMPLIFIED              => "УСН Доходы",
            static::TAXMODE_SIMPLIFIED_WITH_EXPENSE => "УСН Доход минус расход",
            static::TAXMODE_ENVD                    => "ЕНВД",
            static::TAXMODE_PATENT                  => "ПСН",
            static::TAXMODE_COMMON_AGRICULTURAL     => "ЕСХН",
        ];
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            HasJsonFieldsBehavior::class => [
                'class'  => HasJsonFieldsBehavior::class,
                'fields' => [
                    'moneyPositions',
                    'inventPositions',
                ],
            ],
        ]);
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [

            [
                [
                    'created_at',
                    'updated_at',
                    'created_by',
                    'updated_by',
                    'cms_site_id',
                    'shop_store_id',
                    'shop_cashebox_id',
                    'shop_cashebox_shift_id',
                    'shop_order_id',
                    'cms_user_id',
                    'cashier_cms_user_id',
                    'fiscal_date_at',
                    'is_print',
                ],
                'integer',
            ],
            [['amount'], 'number'],
            [['error_message', 'provider_request_data', 'provider_response_data'], 'string'],
            [
                [
                    'status',
                    'doc_type',
                    'email',
                    'cashier_name',
                    'cashier_position',
                    'tax_mode',
                    'fiscal_date',
                    'fiscal_kkt_number',
                    'fiscal_fn_number',
                    'fiscal_fn_doc_number',
                    'fiscal_fn_doc_mark',
                    'fiscal_shift_number',
                    'fiscal_check_number',
                    'fiscal_ecr_registration_umber',
                    'qr',
                    'provider_uid',
                    'seller_address',
                    'seller_name',
                    'seller_inn',
                    'kkm_payments_address',
                ],
                'string',
                'max' => 255,
            ],

            [['cashier_cms_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['cashier_cms_user_id' => 'id']],
            [['cms_site_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsSite::className(), 'targetAttribute' => ['cms_site_id' => 'id']],
            [['cms_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['cms_user_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['shop_cashebox_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopCashebox::className(), 'targetAttribute' => ['shop_cashebox_id' => 'id']],
            [['shop_cashebox_shift_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopCasheboxShift::className(), 'targetAttribute' => ['shop_cashebox_shift_id' => 'id']],
            [['shop_order_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopOrder::className(), 'targetAttribute' => ['shop_order_id' => 'id']],
            [['shop_store_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopStore::className(), 'targetAttribute' => ['shop_store_id' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['updated_by' => 'id']],


            [
                [
                    'doc_type',
                ],
                'in',
                'range' => array_keys(static::docTypes()),
            ],

            [
                [
                    'doc_type',
                ],
                'default',
                'value' => self::DOCTYPE_SALE,
            ],

            [
                [
                    'status',
                ],
                'in',
                'range' => array_keys(static::statuses()),
            ],
            [
                [
                    'status',
                ],
                'default',
                'value' => self::STATUS_NEW,
            ],
            [
                [
                    'email',
                ],
                'default',
                'value' => "check@skeeks.com",
            ],
            [
                [
                    'cashier_position',
                ],
                'default',
                'value' => "Кассир",
            ],

            [
                [
                    'moneyPositions',
                    'inventPositions',
                ],
                'safe',
            ],


            [
                'cms_site_id',
                'default',
                'value' => function () {
                    if (\Yii::$app->skeeks->site) {
                        return \Yii::$app->skeeks->site->id;
                    }
                },
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'cms_site_id'                   => 'Cms Site ID',
            'status'                        => 'Статус',
            'doc_type'                      => 'Тип документа',
            'shop_store_id'                 => 'Магазин',
            'shop_cashebox_id'              => 'Касса',
            'shop_cashebox_shift_id'        => 'Смена',
            'shop_order_id'                 => 'Заказ',
            'cms_user_id'                   => 'Клиент',
            'email'                         => 'Email или телефон клиента',
            'cashier_name'                  => 'Кассир',
            'cashier_position'              => 'Должность',
            'cashier_cms_user_id'           => 'Кассир - пользователь',
            'tax_mode'                      => 'Применяемая система налогообложения',
            'amount'                        => 'Сумма чека',
            'moneyPositions'                => 'Json объект с типом оплаты и суммы платежа',
            'inventPositions'               => 'Json объект с позициями',
            'fiscal_date_at'                => 'Fiscal Date At',
            'fiscal_date'                   => 'Fiscal Date',
            'fiscal_kkt_number'             => 'Fiscal Kkt Number',
            'fiscal_fn_number'              => 'Fiscal Fn Number',
            'fiscal_fn_doc_number'          => 'Fiscal Fn Doc Number',
            'fiscal_fn_doc_mark'            => 'Fiscal Fn Doc Mark',
            'fiscal_shift_number'           => 'Fiscal Shift Number',
            'fiscal_check_number'           => 'Fiscal Check Number',
            'fiscal_ecr_registration_umber' => 'Fiscal Ecr Registration Umber',
            'qr'                            => 'QR код чека',
            'error_message'                 => 'Сообщение об ошибке',
            'provider_uid'                  => 'Уникальный ID внешней системы',
            'provider_request_data'         => 'Данные для создания объекта чека',
            'provider_response_data'        => 'Все данные из внешней системы по чеку',
        ]);
    }
    /**
     * {@inheritdoc}
     */
    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
        ]);
    }


    /**
     * Gets query for [[CmsSite]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCmsSite()
    {
        $siteClass = \Yii::$app->skeeks->siteClass;
        return $this->hasOne($siteClass, ['id' => 'cms_site_id']);
    }

    /**
     * Gets query for [[CashierCmsUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCashierCmsUser()
    {
        return $this->hasOne(\Yii::$app->user->identityClass, ['id' => 'cashier_cms_user_id']);
    }

    /**
     * Gets query for [[CmsUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCmsUser()
    {
        return $this->hasOne(\Yii::$app->user->identityClass, ['id' => 'cms_user_id']);
    }


    /**
     * Gets query for [[ShopCashebox]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopCashebox()
    {
        return $this->hasOne(ShopCashebox::className(), ['id' => 'shop_cashebox_id']);
    }

    /**
     * Gets query for [[ShopCasheboxShift]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopCasheboxShift()
    {
        return $this->hasOne(ShopCasheboxShift::className(), ['id' => 'shop_cashebox_shift_id']);
    }

    /**
     * Gets query for [[ShopOrder]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrder()
    {
        return $this->hasOne(ShopOrder::className(), ['id' => 'shop_order_id']);
    }

    /**
     * Gets query for [[ShopStore]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopStore()
    {
        return $this->hasOne(ShopStore::className(), ['id' => 'shop_store_id']);
    }

    /**
     * @return bool
     */
    public function getIsApproved()
    {
        if ($this->status == static::STATUS_APPROVED) {
            return true;
        }
        return false;
    }

    /**
     * Предмет расчета. Список доступных значений:
        ● commodity – товар
        ● excise – подакцизный товар
        ● job – работа
        ● service – услуга
        ● gambling_bet – ставка азартной игры
        ● gambling_prize – выигрыш азартной игры
        ● lottery – лотерейный билет
        ● lottery_prize – выигрыш лотереи
        ● intellectual_activity – предоставление
        результатов интеллектуальной деятельности
        ● payment – платеж
        ● agent_commission – агентское вознаграждение
        ● composite – составной предмет расчета
        ● another – иной предмет расчета
        ● property_right – имущественное право
        ● sales_tax – торговый сбор
        ● resort_fee – курортный сбор
        Если не указано, то касса проводит продажу с
        предметом расчета ТОВАР

             *
     * @return string[]
     */
    static public function paymentObjects()
    {
         return [
             'commodity' => 'товар',
             'excise' => 'подакцизный товар',
             'job' => 'работа',
             'service' => 'услуга',
             'gambling_bet' => 'ставка азартной игры',
             'gambling_prize' => 'выигрыш азартной игры',
             'lottery' => 'лотерейный билет',
             'lottery_prize' => 'выигрыш лотереи',
             'intellectual_activity' => 'предоставление результатов интеллектуальной деятельности',
             'payment' => 'платеж',
             'agent_commission' => 'агентское вознаграждение',
             'composite' => 'составной предмет расчета',
             'another' => 'иной предмет расчета',
             'property_right' => 'имущественное право',
             'sales_tax' => 'торговый сбор',
             'resort_fee' => 'курортный сбор',
         ];
    }

    /**
     * Признак расчета. Список доступных значений:
        ● full_prepayment – предоплата 100%. Полная
        предварительная оплата до момента передачи
        предмета расчета
        ● prepayment – предоплата. Частичная
        предварительная оплата до момента передачи
        предмета расчета
        ● advance – аванс
        ● full_payment – полный расчет. Полная оплата, в
        том числе с учетом аванса (предварительной
        оплаты) в момент передачи предмета расчета
        ● partial_payment – частичный расчет и кредит.
        Частичная оплата предмета расчета в момент
        его передачи с последующей оплатой в кредит
        ● credit – передача в кредит. Передача предмета
        расчета без его оплаты в момент его передачи с
        последующей оплатой в кредит
        ● credit_payment – оплата кредита. Оплата
        предмета расчета после его передачи с оплатой
        в кредит (оплата кредита)
        Если не указано, то касса проводит продажу с
        признаком расчета ПОЛНЫЙ РАСЧЕТ
             *
     * @return string[]
     */
    static public function paymentMethods()
    {
         return [
             'full_prepayment' => 'предоплата 100%',
             'prepayment' => 'предоплата',
             'advance' => 'аванс',
             'full_payment' => 'полный расчет',
             'partial_payment' => '– частичный расчет и кредит',
             'credit' => 'передача в кредит',
             'credit_payment' => 'оплата кредита',
         ];
    }

    /**
     * Тип оплаты
        ● CARD - безналичная оплата
        ● CASH - оплата наличными
        ● PREPAID - предварительная оплата (зачет
        аванса и (или) предыдущих платежей)
        ● POSTPAY - постоплата (кредит)
        ● OTHER - иная форма оплаты

             *
     * @return string[]
     */
    static public function paymentTypes()
    {
         return [
             'card' => 'безналичная оплата',
             'cash' => 'оплата наличными',
             'prepaid' => 'предварительная оплата',
             'postpay' => 'постоплата (кредит)',
             'other' => 'иная форма оплаты',
         ];
    }

    /**
     * @param string $code
     * @return string
     */
    static public function getPaymentObjectAsText(string $code = "")
    {
        return (string) ArrayHelper::getValue(static::paymentObjects(), $code, 'товар');
    }

    /**
     * @param string $code
     * @return string
     */
    static public function getPaymentMethodAsText(string $code = "")
    {
        return (string) ArrayHelper::getValue(static::paymentMethods(), $code, 'полный расчет');
    }
    /**
     * @param string $code
     * @return string
     */
    static public function getPaymentTypeAsText(string $code = "")
    {
        return (string) ArrayHelper::getValue(static::paymentTypes(), StringHelper::strtolower($code));
    }
}
