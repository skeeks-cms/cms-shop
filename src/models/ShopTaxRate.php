<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 31.08.2015
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsSite;
use skeeks\cms\models\Core;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_tax_rate}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $tax_id
 * @property integer $person_type_id
 * @property string $value
 * @property string $currency
 * @property string $is_percent
 * @property string $is_in_price
 * @property integer $priority
 * @property string $active
 *
 * @property ShopTax $tax
 * @property ShopPersonType $personType
 */
class ShopTaxRate extends Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_tax_rate}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [
                ['created_by', 'updated_by', 'created_at', 'updated_at', 'tax_id', 'person_type_id', 'priority'],
                'integer'
            ],
            [['tax_id', 'person_type_id'], 'required'],
            [['value'], 'number'],
            [['currency'], 'string', 'max' => 3],
            [['is_percent', 'is_in_price', 'active'], 'string', 'max' => 1],

            [['is_in_price'], 'default', 'value' => Cms::BOOL_N],
            [['is_percent', 'active'], 'default', 'value' => Cms::BOOL_Y],
            [['priority'], 'default', 'value' => 100],
            [['value'], 'default', 'value' => 0],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'id' => \Yii::t('skeeks/shop/app', 'ID'),
            'created_by' => \Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by' => \Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at' => \Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at' => \Yii::t('skeeks/shop/app', 'Updated At'),
            'tax_id' => \Yii::t('skeeks/shop/app', 'Income tax'),
            'person_type_id' => \Yii::t('skeeks/shop/app', 'Payer'),
            'value' => \Yii::t('skeeks/shop/app', 'Value'),
            'currency' => \Yii::t('skeeks/shop/app', 'Currency'),
            'is_percent' => \Yii::t('skeeks/shop/app', 'Is Percent'),
            'is_in_price' => \Yii::t('skeeks/shop/app', 'Included in the price'),
            'priority' => \Yii::t('skeeks/shop/app', 'The order of application'),
            'active' => \Yii::t('skeeks/shop/app', 'Active'),
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTax()
    {
        return $this->hasOne(ShopTax::className(), ['id' => 'tax_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPersonType()
    {
        return $this->hasOne(ShopPersonType::className(), ['id' => 'person_type_id']);
    }

}