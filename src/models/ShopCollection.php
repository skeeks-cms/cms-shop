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
use skeeks\cms\models\behaviors\HasStorageFileMulti;
use skeeks\cms\models\CmsStorageFile;
use skeeks\cms\models\StorageFile;
use skeeks\cms\shop\models\queries\ShopCollectionQuery;
use skeeks\cms\shop\urlRules\UrlRuleCollection;
use skeeks\modules\cms\money\models\Currency;
use skeeks\yii2\yaslug\YaSlugBehavior;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Application;

/**
 * @property integer          $id
 * @property integer|null     $created_by
 * @property integer|null     $created_at
 * @property integer|null     $updated_at
 *
 * @property string           $name
 * @property bool             $is_active
 * @property string|null      $description_short
 * @property string|null      $description_full
 * @property integer|null     $cms_image_id
 * @property ineger           $shop_brand_id
 * @property string           $code
 * @property string           $external_id
 *
 * @property string|null      $seo_h1
 * @property string|null      $meta_title
 * @property string|null      $meta_description
 * @property string|null      $meta_keywords
 * @property integer          $priority
 *
 *
 * @property string           $seoName
 * @property string           $url
 * @property string           $absoluteUrl
 * @property ShopBrand        $brand
 * @property CmsStorageFile   $image
 * @property CmsStorageFile[] $images
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopCollection extends ActiveRecord
{
    protected $_image_ids = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_collection}}';
    }


    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            HasStorageFile::class => [
                'class'  => HasStorageFile::class,
                'fields' => ['cms_image_id'],
            ],

            HasStorageFileMulti::className() => [
                'class'     => HasStorageFileMulti::className(),
                'relations' => [
                    [
                        'relation' => 'images',
                        'property' => 'imageIds',
                    ],
                ],
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
                    'shop_brand_id',
                ],
                'integer',
            ],
            [
                [
                    'name',
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
                    'shop_brand_id',
                ],
                'required',
            ],
            //Фильтры и обработчики
            [
                [
                    'name',
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
                    'cms_image_id',
                ],
                'safe',
            ],
            [
                ['cms_image_id'],
                \skeeks\cms\validators\FileValidator::class,
                'skipOnEmpty' => false,
                'extensions'  => ['jpg', 'jpeg', 'gif', 'png', 'webp'],
                'maxFiles'    => 1,
                'maxSize'     => 1024 * 1024 * 10,
                'minSize'     => 256,
            ],

            [['imageIds', 'fileIds'], 'safe'],

            [
                ['imageIds'],
                \skeeks\cms\validators\FileValidator::class,
                'skipOnEmpty' => false,
                'extensions'  => ['jpg', 'jpeg', 'gif', 'png', 'webp'],
                'maxFiles'    => 100,
                'maxSize'     => 1024 * 1024 * 10,
                'minSize'     => 256,
            ],

        ]);
    }

    /**
     * @return array
     */
    public function getImageIds()
    {
        if ($this->_image_ids !== null) {
            return $this->_image_ids;
        }

        if ($this->images) {
            return ArrayHelper::map($this->images, 'id', 'id');
        }

        return [];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImages()
    {
        return $this->hasMany(StorageFile::className(), ['id' => 'storage_file_id'])
            ->viaTable('shop_collection2image', ['shop_collection_id' => 'id'])
            ->orderBy(['priority' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function setImageIds($ids)
    {
        $this->_image_ids = $ids;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id'                => Yii::t('skeeks/shop/app', 'ID'),
            'name'              => Yii::t('skeeks/shop/app', 'Название коллекции'),
            'is_active'         => Yii::t('skeeks/shop/app', 'Отображается на сайте?'),
            'description_short' => Yii::t('skeeks/shop/app', 'Короткое описание'),
            'description_full'  => Yii::t('skeeks/shop/app', 'Подробное описание'),
            'cms_image_id'      => Yii::t('skeeks/shop/app', 'Изображение'),
            'seo_h1'            => Yii::t('skeeks/cms', 'SEO заголовок h1'),
            'meta_title'        => Yii::t('skeeks/cms', 'Meta Title'),
            'meta_keywords'     => Yii::t('skeeks/cms', 'Meta Keywords'),
            'meta_description'  => Yii::t('skeeks/cms', 'Meta Description'),
            'priority'          => Yii::t('skeeks/cms', 'Сортировка'),
            'external_id'       => Yii::t('skeeks/cms', 'Внешний код'),
            'shop_brand_id'     => Yii::t('skeeks/cms', 'Бренд'),
            'imageIds'          => Yii::t('skeeks/cms', 'Images'),
        ]);
    }
    /**
     * {@inheritdoc}
     */
    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
            'name'        => Yii::t('skeeks/shop/app', 'Название бренда, в точности как оно зарегистрировано!'),
            'external_id' => Yii::t('skeeks/shop/app', 'Используется при интеграции со сторонними системами'),
        ]);
    }


    /**
     * @return ShopBrand
     */
    public function getBrand()
    {
        return $this->hasOne(ShopBrand::class, ['id' => 'shop_brand_id']);

    }
    /**
     * @return CmsStorageFile|null
     */
    public function getImage()
    {
        return $this->hasOne(CmsStorageFile::class, ['id' => 'cms_image_id']);

    }

    /**
     * @return ShopCollectionQuery
     */
    public static function find()
    {
        return (new ShopCollectionQuery(get_called_class()));
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
            UrlRuleCollection::$models[$this->id] = $this;
        }

        if ($params) {
            $params = ArrayHelper::merge(['/shop/collection/view', 'model' => $this], $params);
        } else {
            $params = ['/shop/collection/view', 'model' => $this];
        }

        return Url::to($params, $scheme);
    }
}