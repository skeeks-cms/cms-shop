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
use skeeks\cms\money\models\MoneyCurrency;
use skeeks\cms\money\Money;
use skeeks\cms\shop\helpers\DiscountConditionHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%shop_discount}}".
 *
 * @property integer                  $id
 * @property integer                  $created_by
 * @property integer                  $updated_by
 * @property integer                  $created_at
 * @property integer                  $updated_at
 * @property integer                  $cms_site_id
 * @property string                   $active
 * @property integer                  $active_from
 * @property integer                  $active_to
 * @property string                   $name
 * @property string                   $max_discount
 * @property string                   $value_type
 * @property string                   $value
 * @property string                   $currency_code
 * @property string                   $min_order_sum
 * @property string                   $notes
 * @property integer                  $type
 * @property string                   $xml_id
 * @property integer                  $priority
 * @property string                   $last_discount
 * @property string                   $conditions
 * @property string                   $assignment_type Тип назначения скидки (на товар, на корзину)
 *
 * @property string                   $permissionName
 *
 * @property Currency                 $currencyCode
 * @property CmsSite                  $cmsSite
 * @property ShopDiscount2typePrice[] $shopDiscount2typePrices
 * @property ShopTypePrice[]          $typePrices
 *
 * @property bool                     $isLast
 */
class ShopDiscount extends \skeeks\cms\models\Core
{
    CONST VALUE_TYPE_P = "P";
    CONST VALUE_TYPE_F = "F";
    CONST VALUE_TYPE_S = "S";

    CONST ASSIGNMENT_TYPE_PRODUCT = "product";
    CONST ASSIGNMENT_TYPE_CART = "cart";

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

    static public function getAssignmentTypes()
    {
        return [
            self::ASSIGNMENT_TYPE_PRODUCT => "Скидка на товар",
            self::ASSIGNMENT_TYPE_CART => "Скидка на корзину",
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
            RelationalBehavior::class,
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
                    'cms_site_id',
                    'active_from',
                    'active_to',
                    'max_uses',
                    'type',
                    'priority',
                ],
                'integer',
            ],
            [['max_discount', 'value', 'min_order_sum'], 'number'],
            [['currency_code', 'name'], 'required'],
            [['conditions', 'unpack'], 'string'],
            [['assignment_type'], 'string'],
            [
                ['active', 'value_type', 'last_discount'],
                'string',
                'max' => 1,
            ],
            [['name', 'notes'], 'string', 'max' => 255],
            [['currency_code'], 'string', 'max' => 3],
            [['active', 'last_discount'], 'default', 'value' => Cms::BOOL_Y],
            [['type'], 'default', 'value' => self::TYPE_DEFAULT],
            [['value_type'], 'default', 'value' => self::VALUE_TYPE_P],
            [['assignment_type'], 'default', 'value' => self::ASSIGNMENT_TYPE_PRODUCT],
            [['value'], 'default', 'value' => 0],
            [['priority'], 'default', 'value' => 1],
            ['typePrices', 'safe'], // allow set permissions with setAttributes()
            
            [
                'cms_site_id',
                'default',
                'value' => function () {
                    if (\Yii::$app->skeeks->site) {
                        return \Yii::$app->skeeks->site->id;
                    }
                },
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => \Yii::t('skeeks/shop/app', 'ID'),
            'created_by'    => \Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by'    => \Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at'    => \Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at'    => \Yii::t('skeeks/shop/app', 'Updated At'),
            'cms_site_id'       => \Yii::t('skeeks/shop/app', 'Site'),
            'active'        => \Yii::t('skeeks/shop/app', 'Active'),
            'active_from'   => \Yii::t('skeeks/shop/app', 'Active from'),
            'active_to'     => \Yii::t('skeeks/shop/app', 'Active to'),
            'name'          => \Yii::t('skeeks/shop/app', 'Name'),
            'max_discount'  => \Yii::t('skeeks/shop/app',
                'The maximum amount of discount (in currency of discount ; 0 - the discount is not limited to)'),
            'value_type'    => \Yii::t('skeeks/shop/app', 'Discount Type'),
            'value'         => \Yii::t('skeeks/shop/app', 'Markdown'),
            'currency_code' => \Yii::t('skeeks/shop/app', 'Currency discount'),
            'min_order_sum' => \Yii::t('skeeks/shop/app', 'Min Order Sum'),
            'notes'         => \Yii::t('skeeks/shop/app', 'Short description (up to 255 characters)'),
            'type'          => \Yii::t('skeeks/shop/app', 'Type'),
            'priority'      => \Yii::t('skeeks/shop/app', 'Priority applicability'),
            'last_discount' => \Yii::t('skeeks/shop/app', 'Stop further application of discounts'),
            'conditions'    => \Yii::t('skeeks/shop/app', 'Conditions'),
            'typePrices'    => \Yii::t('skeeks/shop/app', 'Types of prices, to which the discount is applicable'),
            'assignment_type'    => "Назначение скидки",
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
    public function getCmsSite()
    {
        return $this->hasOne(CmsSite::class, ['id' => 'cms_site_id']);
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
        return "shop-discount-".$this->id;
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

        if ($this->cms_site_id) {
            if ($this->cms_site_id != \Yii::$app->cms->cmsSite->id) {
                return false;
            }
        }

        //Назначение скидки - товарная скидка
        if ($this->assignment_type != ShopDiscount::ASSIGNMENT_TYPE_PRODUCT) {
            return false;
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
            'data'                  => $conditions,
            'shopCmsContentElement' => $shopCmsContentElement,
        ]);

        return $condition->isTrue;
    }

    public function getIsLast()
    {
        return ($this->last_discount == "Y");
    }
}