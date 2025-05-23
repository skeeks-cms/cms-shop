<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */


use skeeks\cms\models\behaviors\HasJsonFieldsBehavior;
use skeeks\cms\shop\models\ShopMarketplace;
use yii\helpers\ArrayHelper;

/**
 * @property int|null          $updated_at
 * @property int               $wb_updated_at
 * @property string            $wb_updated_at_string
 * @property int               $shop_marketplace_id
 * @property int|null          $shop_product_id
 * @property string            $vendor_code Артикул продавца
 * @property int|null          $imt_id Идентификатор карточки товара
 * @property int               $wb_id Артикул WB
 * @property string|null       $brand Брэнд
 * @property string|null       $wb_object Категория для который создавалось КТ с данной НМ
 * @property int|null          $wb_object_id Идентификатор предмета
 *
 * @property float             $price Идентификатор предмета
 * @property int               $discount Скидка
 * @property float             $promo_code Промокод
 *
 * @property array             $wb_data Массив данные по товару из wb
 *
 * @property ShopMarketplace[] $shopMarketplace
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopWbProduct extends \skeeks\cms\base\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shop_wb_product}}';
    }


    public function init()
    {
        $this->on(self::EVENT_AFTER_FIND, function () {
            $this->price = (float)$this->price;
            $this->promo_code = (float)$this->promo_code;
        });
        return parent::init();
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            HasJsonFieldsBehavior::className() => [
                'class'  => HasJsonFieldsBehavior::className(),
                'fields' => ['wb_data'],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [

            [['updated_at'], 'integer'],
            [
                [
                    'wb_updated_at',
                    'shop_marketplace_id',
                    'shop_product_id',
                    'imt_id',
                    'wb_id',
                    'discount',
                    'wb_object_id',
                ],
                'integer',
            ],

            [
                [
                    'wb_updated_at_string',
                    'vendor_code',
                    'brand',
                    'wb_object',
                ],
                'string',
                'max' => 255,
            ],

            [['wb_data'], 'safe'],

            [['wb_id'], 'unique'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [


        ]);
    }
    /**
     * {@inheritdoc}
     */
    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [

        ]);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopMarketplace()
    {
        return $this->hasOne(ShopMarketplace::class, ['id' => 'shop_marketplace_id']);
    }


}