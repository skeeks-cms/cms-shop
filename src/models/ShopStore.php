<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\models\behaviors\HasJsonFieldsBehavior;
use skeeks\cms\models\behaviors\HasStorageFile;
use skeeks\cms\models\CmsSite;
use skeeks\cms\models\CmsStorageFile;
use skeeks\cms\models\StorageFile;
use yii\helpers\ArrayHelper;

/**
 * @property string             $name
 * @property string             $description
 * @property int                $cms_image_id
 * @property bool               $is_active
 * @property string|null        $external_id
 * @property integer|null       $cms_site_id
 * @property string|null        $address Полный адрес
 * @property float|null         $latitude Широта
 * @property float|null         $longitude Долгота
 * @property string|null        $work_time Рабочее время
 * @property int                $priority
 * @property bool               $is_supplier
 * @property string             $source_selling_price
 * @property string             $source_purchase_price
 * @property float              $purchase_extra_charge
 * @property float              $selling_extra_charge
 *
 * @property string             $coordinates
 *
 * @property CmsStorageFile     $cmsImage
 * @property CmsSite            $cmsSite
 * @property ShopStoreProduct[] $shopStoreProducts
 * @property ShopProduct[]      $shopProducts
 * * @property ShopStoreProperty[] $shopStoreProperties
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopStore extends \skeeks\cms\base\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shop_store}}';
    }

    public function init()
    {
        $this->on(self::EVENT_AFTER_FIND, function() {
            $this->selling_extra_charge = (float) $this->selling_extra_charge;
            $this->purchase_extra_charge = (float) $this->purchase_extra_charge;
        });
        return parent::init();
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [

            HasStorageFile::class              => [
                'class'  => HasStorageFile::class,
                'fields' => ['cms_image_id'],
            ],
            HasJsonFieldsBehavior::className() => [
                'class'  => HasJsonFieldsBehavior::className(),
                'fields' => ['work_time'],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at'], 'integer'],
            [['priority'], 'integer'],

            [['name'], 'string', 'max' => 255],
            [['name'], 'required'],

            [['description'], 'string'],
            [['address'], 'string'],
            [['address'], 'default', 'value' => null],

            [['is_active'], 'integer'],

            [['cms_image_id'], 'safe'],

            [['external_id'], 'default', 'value' => null],
            [['external_id'], 'string'],


            [['cms_site_id'], 'integer'],
            [['is_supplier'], 'integer'],
            [['is_supplier'], 'default', 'value' => 0],

            [
                'cms_site_id',
                'default',
                'value' => function () {
                    if (\Yii::$app->skeeks->site) {
                        return \Yii::$app->skeeks->site->id;
                    }
                },
            ],

            [
                ['cms_site_id', 'external_id'],
                'unique',
                'targetAttribute' => ['cms_site_id', 'external_id'],
                'when'            => function (self $model) {
                    return (bool)$model->external_id;
                },
            ],

            [['name', 'cms_site_id'], 'unique', 'targetAttribute' => ['name', 'cms_site_id']],

            [['latitude', 'longitude'], 'number'],
            [['work_time'], 'safe'],

            [
                [
                    'latitude',
                    'longitude',
                ],
                function ($attribute) {
                    if ($this->{$attribute} && $this->{$attribute} <= 0) {
                        $this->addError($attribute, 'Адрес указан некорректно');
                        return false;
                    }
                    return true;
                },
            ],

            [['source_selling_price', 'source_purchase_price'], 'string', 'max' => 255],

            /*[[
                'purchase_extra_charge', 'selling_extra_charge',
                'source_selling_price', 'source_purchase_price'
            ], 'required'],*/

            [['purchase_extra_charge', 'selling_extra_charge'], 'number'],
            /*[
                ['purchase_extra_charge', 'selling_extra_charge'],
                'in',
                [
                    'selling_price',
                    'purchase_price',
                ],
            ],*/

            [['source_selling_price'], 'default', 'value' => "selling_price"],
            [['source_purchase_price'], 'default', 'value' => "purchase_price"],

            [['purchase_extra_charge', 'selling_extra_charge'], 'default', 'value' => 100],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [

            'name'                  => "Название",
            'description'           => "Описание",
            'cms_image_id'          => "Изображение",
            'is_active'             => "Активность",
            'external_id'           => "ID из внешней системы",
            'priority'              => "Сортировка",
            'work_time'             => 'Время работы',
            'latitude'              => 'Широта',
            'longitude'             => 'Долгота',
            'address'               => 'Адрес',
            'coordinates'           => '',
            'is_supplier'           => 'Поставщик?',
            'source_selling_price'  => 'Цена поставщика',
            'source_purchase_price' => 'Цена поставщика',
            'purchase_extra_charge' => 'Наценка',
            'selling_extra_charge'  => 'Наценка',
        ]);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsImage()
    {
        return $this->hasOne(StorageFile::class, ['id' => 'cms_image_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsSite()
    {
        return $this->hasOne(CmsSite::class, ['id' => 'cms_site_id']);
    }


    /**
     * Gets query for [[ShopStoreProducts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopStoreProducts()
    {
        return $this->hasMany(ShopStoreProduct::className(), ['shop_store_id' => 'id']);
    }

    /**
     * Gets query for [[ShopProducts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopProducts()
    {
        return $this->hasMany(ShopProduct::className(), ['id' => 'shop_product_id'])->viaTable('shop_store_product', ['shop_store_id' => 'id']);
    }

    /**
     * @return string
     */
    public function getCoordinates()
    {
        if (!$this->latitude || !$this->longitude) {
            return '';
        }

        return $this->latitude.",".$this->longitude;
    }

    /**
     * Режим работы
     *
     * @return array|string|null
     */
    public function getWorkTime()
    {
        if ($this->work_time) {
            return $this->work_time;
        }

        return "";
    }


    /**
     * Gets query for [[ShopSupplierProperties]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopStoreProperties()
    {
        return $this->hasMany(ShopStoreProperty::className(), ['shop_store_id' => 'id'])->orderBy(['priority' => SORT_ASC]);
    }

}