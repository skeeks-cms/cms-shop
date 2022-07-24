<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\shop\models\queries\ShopCasheboxShiftQuery;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "shop_cashebox_shift".
 *
 * @property int          $id
 * @property int          $shop_cashebox_id
 * @property int|null     $created_by
 * @property int|null     $created_at
 * @property int|null     $closed_at
 * @property int|null     $closed_by
 * @property int          $shift_number
 *
 * @property ShopCashebox $shopCashebox
 */
class ShopCasheboxShift extends \skeeks\cms\base\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shop_cashebox_shift}}';
    }


    public function init()
    {
        $this->on(self::EVENT_BEFORE_UPDATE, [$this, "_beforeUpdate"]);
        $this->on(self::EVENT_BEFORE_INSERT, [$this, "_beforeInsert"]);

        return parent::init();
    }

    public function _beforeUpdate($e)
    {
        if ($this->isAttributeChanged("closed_at")) {
            $this->closed_by = \Yii::$app->user->id;
        }
    }

    public function _beforeInsert($e)
    {
        //Если смена уже открыта этим пользователем
        if (static::find()->cachebox($this->shopCashebox->id)->notClosed()->exists()) {
            throw new Exception("Смена на кассе уже открыта! Возможно в ней работает другой кассир!");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['shop_cashebox_id'], 'required'],

            [['shop_cashebox_id', 'shift_number', 'created_by', 'created_at', 'closed_at', 'closed_by'], 'integer'],
            [['shop_cashebox_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopCashebox::className(), 'targetAttribute' => ['shop_cashebox_id' => 'id']],
            [['created_by'], 'default', 'value' => function() {
                return \Yii::$app->user->id;
            }],

            [
                ['shift_number'],
                'default',
                'value' => function () {
                    $shopCasheboxShift = ShopCasheboxShift::find()->cachebox($this->shop_cashebox_id)->orderBy(['id' => SORT_DESC])->one();
                    if ($shopCasheboxShift) {
                        return $shopCasheboxShift->shift_number + 1;
                    } else {
                        return 1;
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
            'id'               => 'ID',
            'shift_number'     => 'Номер смены',
            'shop_cashebox_id' => 'Касса',
            'created_by'       => 'Кассир',
            'created_at'       => 'Открыта',
            'closed_at'        => 'Закрыта',
            'closed_by'        => 'Закрыл смену',
        ]);
    }


    /**
     * Gets query for [[ShopCachebox]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopCashebox()
    {
        return $this->hasOne(ShopCashebox::class, ['id' => 'shop_cashebox_id']);
    }

    /**
     * @return \skeeks\cms\query\CmsActiveQuery|ShopStoreQuery
     */
    public static function find()
    {
        return new ShopCasheboxShiftQuery(get_called_class());
    }
}