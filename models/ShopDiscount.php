<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 10.10.2015
 */
namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsUser;
use skeeks\modules\cms\money\models\Currency;
use Yii;

/**
 * This is the model class for table "{{%shop_discount}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $site_id
 * @property string $active
 * @property integer $active_from
 * @property integer $active_to
 * @property string $renewal
 * @property string $name
 * @property integer $max_uses
 * @property integer $count_uses
 * @property string $coupon
 * @property string $max_discount
 * @property string $value_type
 * @property string $value
 * @property string $currency_code
 * @property string $min_order_sum
 * @property string $notes
 * @property integer $type
 * @property string $xml_id
 * @property string $count_period
 * @property integer $count_size
 * @property string $count_type
 * @property integer $count_from
 * @property integer $count_to
 * @property integer $action_size
 * @property string $action_type
 * @property integer $priority
 * @property string $last_discount
 * @property string $conditions
 * @property string $unpack
 * @property integer $version
 *
 * @property Currency $currencyCode
 */
class ShopDiscount extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_discount}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'site_id', 'active_from', 'active_to', 'max_uses', 'count_uses', 'type', 'count_size', 'count_from', 'count_to', 'action_size', 'priority', 'version'], 'integer'],
            [['max_discount', 'value', 'min_order_sum'], 'number'],
            [['currency_code'], 'required'],
            [['conditions', 'unpack'], 'string'],
            [['active', 'renewal', 'value_type', 'count_period', 'count_type', 'action_type', 'last_discount'], 'string', 'max' => 1],
            [['name', 'notes', 'xml_id'], 'string', 'max' => 255],
            [['coupon'], 'string', 'max' => 20],
            [['currency_code'], 'string', 'max' => 3]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'site_id' => Yii::t('app', 'Site ID'),
            'active' => Yii::t('app', 'Active'),
            'active_from' => Yii::t('app', 'Active From'),
            'active_to' => Yii::t('app', 'Active To'),
            'renewal' => Yii::t('app', 'Renewal'),
            'name' => Yii::t('app', 'Name'),
            'max_uses' => Yii::t('app', 'Max Uses'),
            'count_uses' => Yii::t('app', 'Count Uses'),
            'coupon' => Yii::t('app', 'Coupon'),
            'max_discount' => Yii::t('app', 'Max Discount'),
            'value_type' => Yii::t('app', 'Value Type'),
            'value' => Yii::t('app', 'Value'),
            'currency_code' => Yii::t('app', 'Currency Code'),
            'min_order_sum' => Yii::t('app', 'Min Order Sum'),
            'notes' => Yii::t('app', 'Notes'),
            'type' => Yii::t('app', 'Type'),
            'xml_id' => Yii::t('app', 'Xml ID'),
            'count_period' => Yii::t('app', 'Count Period'),
            'count_size' => Yii::t('app', 'Count Size'),
            'count_type' => Yii::t('app', 'Count Type'),
            'count_from' => Yii::t('app', 'Count From'),
            'count_to' => Yii::t('app', 'Count To'),
            'action_size' => Yii::t('app', 'Action Size'),
            'action_type' => Yii::t('app', 'Action Type'),
            'priority' => Yii::t('app', 'Priority'),
            'last_discount' => Yii::t('app', 'Last Discount'),
            'conditions' => Yii::t('app', 'Conditions'),
            'unpack' => Yii::t('app', 'Unpack'),
            'version' => Yii::t('app', 'Version'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrencyCode()
    {
        return $this->hasOne(Currency::className(), ['code' => 'currency_code']);
    }


}