<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 10.10.2015
 */
namespace skeeks\cms\shop\models;

use skeeks\cms\behaviors\RelationalBehavior;
use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsSite;
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
 * @property string $permissionName
 *
 * @property Currency $currencyCode
 * @property CmsSite $site
 * @property ShopDiscount2typePrice[] $shopDiscount2typePrices
 * @property ShopTypePrice[] $typePrices
 */
class ShopDiscount extends \skeeks\cms\models\Core
{
    CONST VALUE_TYPE_P = "P";
    CONST VALUE_TYPE_F = "F";
    CONST VALUE_TYPE_S = "S";

    const TYPE_DEFAULT          = 0;
    const TYPE_DISCOUNT_SAVE    = 1; //накопительная скидка

    static public function getValueTypes()
    {
        return [
            self::VALUE_TYPE_P => "В процентах",
            self::VALUE_TYPE_F => "Фиксированная сумма",
            self::VALUE_TYPE_S => "Установить цену на товар",
        ];
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_discount}}';
    }

    public function behaviors()
    {
        return [
            RelationalBehavior::className()
        ];
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'site_id', 'active_from', 'active_to', 'max_uses', 'count_uses', 'type', 'count_size', 'count_from', 'count_to', 'action_size', 'priority', 'version'], 'integer'],
            [['max_discount', 'value', 'min_order_sum'], 'number'],
            [['currency_code', 'name'], 'required'],
            [['conditions', 'unpack'], 'string'],
            [['active', 'renewal', 'value_type', 'count_period', 'count_type', 'action_type', 'last_discount'], 'string', 'max' => 1],
            [['name', 'notes', 'xml_id'], 'string', 'max' => 255],
            [['coupon'], 'string', 'max' => 20],
            [['currency_code'], 'string', 'max' => 3],
            [['active', 'last_discount'], 'default', 'value' => Cms::BOOL_Y],
            [['type'], 'default', 'value' => self::TYPE_DEFAULT],
            [['value_type'], 'default', 'value' => self::VALUE_TYPE_P],
            [['value'], 'default', 'value' =>  0],
            [['priority'], 'default', 'value' =>  1],
            ['typePrices', 'safe'], // allow set permissions with setAttributes()
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
            'site_id' => Yii::t('app', 'Site'),
            'active' => Yii::t('app', 'Active'),
            'active_from' => Yii::t('app', 'Active From'),
            'active_to' => Yii::t('app', 'Active To'),
            'renewal' => Yii::t('app', 'Renewal'),
            'name' => Yii::t('app', 'Name'),
            'max_uses' => Yii::t('app', 'Max Uses'),
            'count_uses' => Yii::t('app', 'Count Uses'),
            'coupon' => Yii::t('app', 'Coupon'),
            'max_discount' => Yii::t('app', 'Максимальная сумма скидки (в валюте скидки; 0 - скидка не ограничена)'),
            'value_type' => Yii::t('app', 'Тип скидки'),
            'value' => Yii::t('app', 'Величина скидки'),
            'currency_code' => Yii::t('app', 'Валюта скидки'),
            'min_order_sum' => Yii::t('app', 'Min Order Sum'),
            'notes' => Yii::t('app', 'Краткое описание (до 255 символов)'),
            'type' => Yii::t('app', 'Type'),
            'xml_id' => Yii::t('app', 'Xml ID'),
            'count_period' => Yii::t('app', 'Count Period'),
            'count_size' => Yii::t('app', 'Count Size'),
            'count_type' => Yii::t('app', 'Count Type'),
            'count_from' => Yii::t('app', 'Count From'),
            'count_to' => Yii::t('app', 'Count To'),
            'action_size' => Yii::t('app', 'Action Size'),
            'action_type' => Yii::t('app', 'Action Type'),
            'priority' => Yii::t('app', 'Приоритет применимости'),
            'last_discount' => Yii::t('app', 'Прекратить дальнейшее применение скидок'),
            'conditions' => Yii::t('app', 'Conditions'),
            'unpack' => Yii::t('app', 'Unpack'),
            'version' => Yii::t('app', 'Version'),
            'typePrices' => Yii::t('app', 'Типы цен, к которым применима скидка'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrencyCode()
    {
        return $this->hasOne(Currency::className(), ['code' => 'currency_code']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(CmsSite::className(), ['id' => 'site_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopDiscount2typePrices()
    {
        return $this->hasMany(ShopDiscount2typePrice::className(), ['discount_id' => 'id']);
    }

    /**
     * @return $this
     */
    public function getTypePrices()
    {
        return $this->hasMany(ShopTypePrice::className(), ['id' => 'type_price_id'])
            ->viaTable('{{%shop_discount2type_price}}', ['discount_id' => 'id']);
    }




    /**
     * @return string
     */
    public function getPermissionName()
    {
        return "shop-discount-" . $this->id;
    }


}