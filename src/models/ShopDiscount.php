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
use skeeks\cms\money\models\MoneyCurrency;
use skeeks\cms\shop\helpers\DiscountConditionHelper;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

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

    const TYPE_DEFAULT = 0;
    const TYPE_DISCOUNT_SAVE = 1; //накопительная скидка

    static public function getValueTypes()
    {
        return [
            self::VALUE_TYPE_P => \Yii::t('skeeks/shop/app', 'In percentages'),
            self::VALUE_TYPE_F => \Yii::t('skeeks/shop/app', 'Fixed amount'),
            self::VALUE_TYPE_S => \Yii::t('skeeks/shop/app', 'Set the price for the goods'),
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
            RelationalBehavior::class
        ];
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'created_by',
                    'updated_by',
                    'created_at',
                    'updated_at',
                    'site_id',
                    'active_from',
                    'active_to',
                    'max_uses',
                    'count_uses',
                    'type',
                    'count_size',
                    'count_from',
                    'count_to',
                    'action_size',
                    'priority',
                    'version'
                ],
                'integer'
            ],
            [['max_discount', 'value', 'min_order_sum'], 'number'],
            [['currency_code', 'name'], 'required'],
            [['conditions', 'unpack'], 'string'],
            [
                ['active', 'renewal', 'value_type', 'count_period', 'count_type', 'action_type', 'last_discount'],
                'string',
                'max' => 1
            ],
            [['name', 'notes', 'xml_id'], 'string', 'max' => 255],
            [['coupon'], 'string', 'max' => 20],
            [['currency_code'], 'string', 'max' => 3],
            [['active', 'last_discount'], 'default', 'value' => Cms::BOOL_Y],
            [['type'], 'default', 'value' => self::TYPE_DEFAULT],
            [['value_type'], 'default', 'value' => self::VALUE_TYPE_P],
            [['value'], 'default', 'value' => 0],
            [['priority'], 'default', 'value' => 1],
            ['typePrices', 'safe'], // allow set permissions with setAttributes()
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('skeeks/shop/app', 'ID'),
            'created_by' => \Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by' => \Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at' => \Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at' => \Yii::t('skeeks/shop/app', 'Updated At'),
            'site_id' => \Yii::t('skeeks/shop/app', 'Site'),
            'active' => \Yii::t('skeeks/shop/app', 'Active'),
            'active_from' => \Yii::t('skeeks/shop/app', 'Active from'),
            'active_to' => \Yii::t('skeeks/shop/app', 'Active to'),
            'renewal' => \Yii::t('skeeks/shop/app', 'Renewal'),
            'name' => \Yii::t('skeeks/shop/app', 'Name'),
            'max_uses' => \Yii::t('skeeks/shop/app', 'Max Uses'),
            'count_uses' => \Yii::t('skeeks/shop/app', 'Count Uses'),
            'coupon' => \Yii::t('skeeks/shop/app', 'Coupon'),
            'max_discount' => \Yii::t('skeeks/shop/app',
                'The maximum amount of discount (in currency of discount ; 0 - the discount is not limited to)'),
            'value_type' => \Yii::t('skeeks/shop/app', 'Discount Type'),
            'value' => \Yii::t('skeeks/shop/app', 'Markdown'),
            'currency_code' => \Yii::t('skeeks/shop/app', 'Currency discount'),
            'min_order_sum' => \Yii::t('skeeks/shop/app', 'Min Order Sum'),
            'notes' => \Yii::t('skeeks/shop/app', 'Short description (up to 255 characters)'),
            'type' => \Yii::t('skeeks/shop/app', 'Type'),
            'xml_id' => \Yii::t('skeeks/shop/app', 'Xml ID'),
            'count_period' => \Yii::t('skeeks/shop/app', 'Count Period'),
            'count_size' => \Yii::t('skeeks/shop/app', 'Count Size'),
            'count_type' => \Yii::t('skeeks/shop/app', 'Count Type'),
            'count_from' => \Yii::t('skeeks/shop/app', 'Count From'),
            'count_to' => \Yii::t('skeeks/shop/app', 'Count To'),
            'action_size' => \Yii::t('skeeks/shop/app', 'Action Size'),
            'action_type' => \Yii::t('skeeks/shop/app', 'Action Type'),
            'priority' => \Yii::t('skeeks/shop/app', 'Priority applicability'),
            'last_discount' => \Yii::t('skeeks/shop/app', 'Stop further application of discounts'),
            'conditions' => \Yii::t('skeeks/shop/app', 'Conditions'),
            'unpack' => \Yii::t('skeeks/shop/app', 'Unpack'),
            'version' => \Yii::t('skeeks/shop/app', 'Version'),
            'typePrices' => \Yii::t('skeeks/shop/app', 'Types of prices, to which the discount is applicable'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrencyCode()
    {
        return $this->hasOne(MoneyCurrency::class, ['code' => 'currency_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(CmsSite::class, ['id' => 'site_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopDiscount2typePrices()
    {
        return $this->hasMany(ShopDiscount2typePrice::class, ['discount_id' => 'id']);
    }

    /**
     * @return $this
     */
    public function getTypePrices()
    {
        return $this->hasMany(ShopTypePrice::class, ['id' => 'type_price_id'])
            ->viaTable('{{%shop_discount2type_price}}', ['discount_id' => 'id']);
    }


    /**
     * @return string
     */
    public function getPermissionName()
    {
        return "shop-discount-" . $this->id;
    }

    /**
     * @param ShopCmsContentElement $shopCmsContentElement
     * @return bool|void
     */
    public function isTrue(ShopCmsContentElement $shopCmsContentElement, ShopProductPrice $shopProductPrice)
    {
        /**
         * Если в скидке указаны условия применения цен
         */
        if ($this->typePrices) {
            $ids = ArrayHelper::map($this->typePrices, 'id', 'id');
            if (!in_array($shopProductPrice->type_price_id, $ids)) {
                return false;
            }
        }
        
        if ($this->site_id) {
            if ($this->site_id != \Yii::$app->cms->cmsSite->id) {
                return false;
            }
        }
        
        return $this->isTrueConditions($shopCmsContentElement, $shopProductPrice);
    }

    /**
     * @param ShopCmsContentElement $shopCmsContentElement
     * @return bool|void
     */
    public function isTrueConditions(ShopCmsContentElement $shopCmsContentElement, ShopProductPrice $shopProductPrice)
    {
        if (!$this->conditions) {
            return true;
        }

        try {
            $conditions = Json::decode($this->conditions);
        } catch (\Exception $e) {
            $conditions = [];
        }

        if (!$this->conditions) {
            return true;
        }

        $condition = new DiscountConditionHelper([
            'data' => $conditions,
            'shopCmsContentElement' => $shopCmsContentElement,
        ]);

        return $condition->isTrue;
    }
}