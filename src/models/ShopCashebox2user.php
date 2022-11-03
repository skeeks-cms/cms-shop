<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsSite;
use skeeks\cms\money\Money;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "shop_cashebox".
 *
 * @property int                 $id
 * @property int                 $cms_site_id
 * @property string              $name
 * @property int|null            $shop_store_id
 * @property int                 $is_active
 * @property int|null            $shop_cloudkassa_id
 *
 * @property float               $balanceCash
 * @property Money               $balanceCashMoney
 *
 * @property CmsSite             $cmsSite
 * @property ShopCasheboxShift[] $shopCasheboxShifts
 * @property ShopOrder[]         $shopOrders
 * @property ShopPayment[]       $shopPayments
 * @property ShopStore           $shopStore
 * @property ShopCloudkassa      $shopCloudkassa
 */
class ShopCashebox2user extends \skeeks\cms\base\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shop_cashebox}}';
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [

            [['is_active'], 'integer'],
            [['shop_cloudkassa_id'], 'integer'],
            [['name'], 'required'],
            [['cms_site_id', 'shop_store_id'], 'integer'],
            [['is_active'], 'integer'],
            [['priority'], 'integer'],
            [['is_active'], 'default', 'value' => 1],
            [['priority'], 'default', 'value' => 1],
            [['name'], 'string', 'max' => 255],
            [['cms_site_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsSite::className(), 'targetAttribute' => ['cms_site_id' => 'id']],
            [['shop_store_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopStore::className(), 'targetAttribute' => ['shop_store_id' => 'id']],
            //[['shop_cloudkassa_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopCloudkassa::class, 'targetAttribute' => ['shop_store_id' => 'id']],

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
            'id'                 => 'ID',
            'cms_site_id'        => 'Сайт',
            'name'               => 'Название',
            'shop_store_id'      => 'Магазин',
            'is_active'          => 'Активность',
            'shop_cloudkassa_id' => 'Облачная касса',
            'priority'           => 'Сортировка',
        ]);
    }
    /**
     * {@inheritdoc}
     */
    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'shop_cloudkassa_id' => 'Если выбрана облачная касса, то фискализация будет происходить через нее.',
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
     * Gets query for [[ShopCasheboxShifts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopCasheboxShifts()
    {
        return $this->hasMany(ShopCasheboxShift::className(), ['shop_cashebox_id' => 'id']);
    }

    /**
     * Gets query for [[ShopCasheboxShifts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopPayments()
    {
        return $this->hasMany(ShopPayment::className(), ['shop_cashebox_id' => 'id']);
    }

    /**
     * Gets query for [[ShopCasheboxShifts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrders()
    {
        return $this->hasMany(ShopOrder::className(), ['shop_cashebox_id' => 'id']);
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
     * Gets query for [[ShopStore]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopCloudkassa()
    {
        return $this->hasOne(ShopCloudkassa::className(), ['id' => 'shop_cloudkassa_id']);
    }

    /**
     * @return float
     */
    public function getBalanceCash()
    {
        $debit = $this->getShopPayments()->select(['sum' => new Expression("SUM(amount)")])->andWhere(['shop_store_payment_type' => ShopPayment::STORE_PAYMENT_TYPE_CASH])->andWhere(['is_debit' => 1])->asArray()->one();
        $creadit = $this->getShopPayments()->select(['sum' => new Expression("SUM(amount)")])->andWhere(['is_debit' => 0])->andWhere(['shop_store_payment_type' => ShopPayment::STORE_PAYMENT_TYPE_CASH])->asArray()->one();

        return (float)(ArrayHelper::getValue($debit, "sum") - ArrayHelper::getValue($creadit, "sum"));
    }

    /**
     * @return Money
     */
    public function getBalanceCashMoney()
    {
        return new Money((string)$this->balanceCash, \Yii::$app->money->currency_code);
    }
}