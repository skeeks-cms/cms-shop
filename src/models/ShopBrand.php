<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 14.09.2015
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\base\ActiveRecord;
use skeeks\cms\models\behaviors\HasStorageFile;
use skeeks\cms\models\CmsCountry;
use skeeks\cms\models\CmsStorageFile;
use skeeks\cms\shop\models\queries\ShopBrandQuery;
use skeeks\cms\shop\urlRules\UrlRuleBrand;
use skeeks\modules\cms\money\models\Currency;
use skeeks\yii2\yaslug\YaSlugBehavior;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * @property integer        $id
 * @property integer|null   $created_by
 * @property integer|null   $created_at
 * @property integer|null   $updated_at
 *
 * @property string         $name
 * @property bool           $is_active
 * @property string|null    $description_short
 * @property string|null    $description_full
 * @property integer|null   $logo_image_id
 * @property string|null    $country_alpha2
 * @property string|null    $website_url
 * @property string         $code
 * @property string         $external_id
 * @property integer|null   $sx_id
 *
 * @property string|null    $seo_h1
 * @property string|null    $meta_title
 * @property string|null    $meta_description
 * @property string|null    $meta_keywords
 * @property integer        $priority
 *
 *
 * @property string         $seoName
 * @property string         $url
 * @property string         $absoluteUrl
 * @property CmsCountry     $country
 * @property CmsStorageFile $logo
 * @property ShopProduct[]  $products
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopBrand extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_brand}}';
    }


    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            HasStorageFile::class => [
                'class'  => HasStorageFile::class,
                'fields' => ['logo_image_id'],
            ],

            YaSlugBehavior::class => [
                'class'         => YaSlugBehavior::class,
                'attribute'     => 'seoName',
                'slugAttribute' => 'code',
                'ensureUnique'  => false,
                'maxLength'     => \Yii::$app->cms->element_max_code_length,
            ],
        ]);
    }

    /**
     * Полное название
     *
     * @return string
     */
    public function getSeoName()
    {
        $result = "";
        if ($this->seo_h1) {
            return $this->seo_h1;
        } else {
            return $this->name;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            //Сущьность полей
            [
                [
                    'created_at',
                    'created_by',
                    'is_active',
                    'priority',
                    'sx_id',
                ],
                'integer',
            ],
            [
                ['sx_id', ], 'default', 'value' => null
            ],
            [
                [
                    'name',
                    'country_alpha2',
                    'website_url',
                    'code',
                    'seo_h1',
                    'meta_title',
                    'meta_description',
                    'meta_keywords',
                    'description_full',
                    'external_id',
                ],
                'string',
            ],
            //Обязательные поля
            [
                [
                    'name',
                ],
                'required',
            ],
            //Фильтры и обработчики
            [
                [
                    'name',
                    'country_alpha2',
                    'website_url',
                    'code',
                    'seo_h1',
                    'meta_title',
                    'meta_description',
                    'meta_keywords',
                    'description_full',
                    'external_id',
                ],
                'trim',
            ],
            //Значения по умолчанию
            [
                [
                    'priority',
                ],
                'default',
                'value' => 500,
            ],
            [
                [
                    'created_at',
                    'created_by',
                    'description_short',
                    'description_full',
                    'country_alpha2',
                    'website_url',
                    'seo_h1',
                    'meta_title',
                    'meta_description',
                    'meta_keywords',
                    'meta_keywords',
                    'external_id',
                ],
                'default',
                'value' => null,
            ],
            //Нестандартные вещи
            [
                [
                    'logo_image_id',
                ],
                'safe',
            ],
            [
                ['logo_image_id'],
                \skeeks\cms\validators\FileValidator::class,
                'skipOnEmpty' => false,
                'extensions'  => ['jpg', 'jpeg', 'gif', 'png', 'webp'],
                'maxFiles'    => 1,
                'maxSize'     => 1024 * 1024 * 10,
                'minSize'     => 256,
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id'                => Yii::t('skeeks/shop/app', 'ID'),
            'name'              => Yii::t('skeeks/shop/app', 'Название бренда'),
            'is_active'         => Yii::t('skeeks/shop/app', 'Отображается на сайте?'),
            'description_short' => Yii::t('skeeks/shop/app', 'Короткое описание'),
            'description_full'  => Yii::t('skeeks/shop/app', 'Подробное описание'),
            'logo_image_id'     => Yii::t('skeeks/shop/app', 'Логотип'),
            'country_alpha2'    => Yii::t('skeeks/shop/app', 'Страна бренда'),
            'website_url'       => Yii::t('skeeks/shop/app', 'Сайт'),
            'seo_h1'            => Yii::t('skeeks/cms', 'SEO заголовок h1'),
            'meta_title'        => Yii::t('skeeks/cms', 'Meta Title'),
            'meta_keywords'     => Yii::t('skeeks/cms', 'Meta Keywords'),
            'meta_description'  => Yii::t('skeeks/cms', 'Meta Description'),
            'priority'          => Yii::t('skeeks/cms', 'Сортировка'),
            'external_id'       => Yii::t('skeeks/cms', 'Внешний код'),
            'sx_id'           => Yii::t('skeeks/cms', 'SkeekS Suppliers ID'),
        ]);
    }
    /**
     * {@inheritdoc}
     */
    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
            'name'           => Yii::t('skeeks/shop/app', 'Название бренда, в точности как оно зарегистрировано!'),
            'country_alpha2' => Yii::t('skeeks/shop/app', 'Основная страна к которой относится этот бренд.'),
            'website_url'    => Yii::t('skeeks/shop/app', 'Полная ссылка на сайт https://'),
            'external_id'    => Yii::t('skeeks/shop/app', 'Используется при интеграции со сторонними системами'),
        ]);
    }


    /**
     * @return CmsCountry|null
     */
    public function getCountry()
    {
        return $this->hasOne(CmsCountry::class, ['alpha2' => 'country_alpha2']);

    }
    /**
     * @return CmsStorageFile|null
     */
    public function getLogo()
    {
        return $this->hasOne(CmsStorageFile::class, ['id' => 'logo_image_id']);

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(ShopProduct::class, ['brand_id' => 'id']);
    }

    /**
     * @return string
     */
    public function getAbsoluteUrl($scheme = false, $params = [])
    {
        return $this->getUrl(true, $params);
    }

    /**
     * @return string
     */
    public function getUrl($scheme = false, $params = [])
    {
        //Это можно использовать только в коротких сценариях, иначе произойдет переполнение памяти
        if (\Yii::$app instanceof Application) {
            UrlRuleBrand::$models[$this->id] = $this;
        }

        if ($params) {
            $params = ArrayHelper::merge(['/shop/brand/view', 'model' => $this], $params);
        } else {
            $params = ['/shop/brand/view', 'model' => $this];
        }

        return Url::to($params, $scheme);
    }

    /**
     * @return ShopBrandQuery
     */
    public static function find()
    {
        return (new ShopBrandQuery(get_called_class()));
    }
}