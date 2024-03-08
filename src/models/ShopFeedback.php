<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\models\behaviors\HasStorageFileMulti;
use skeeks\cms\models\StorageFile;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_payment}}".
 *
 * @property int              $id
 * @property int              $created_at
 * @property int              $updated_at
 * @property int              $created_by
 * @property int              $shop_product_id
 * @property string           $status
 * @property string|null      $message
 * @property string|null      $seller_message
 * @property int|null         $seller_cms_user_id
 * @property int              $rate
 *
 * @property string           $statusAsText
 * @property ShopProduct      $shopProduct
 * @property CmsStorageFile[] $images
 *
 */
class ShopFeedback extends \skeeks\cms\base\ActiveRecord
{
    protected $_image_ids = null;

    const STATUS_ON_MODER = 'on_moder';
    const STATUS_APPROVED = 'approved';
    const STATUS_CLOSED = 'closed';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shop_feedback}}';
    }


    static public function getStatuses()
    {
        return [
            self::STATUS_ON_MODER => 'На модерации',
            self::STATUS_APPROVED => 'Опубликован',
            self::STATUS_CLOSED   => 'Отменен',
        ];
    }

    /**
     * @return string
     */
    public function getStatusAsText()
    {
        return (string)ArrayHelper::getValue(self::getStatuses(), $this->status);
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            HasStorageFileMulti::class => [
                'class'     => HasStorageFileMulti::class,
                'relations' => [
                    [
                        'relation' => 'images',
                        'property' => 'imageIds',
                    ],
                ],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [
                [
                    'shop_product_id',
                    'seller_cms_user_id',
                    'rate',
                ],
                'integer',
            ],
            [
                [
                    'shop_product_id',
                    'rate',
                ],
                'required',
            ],
            [
                [
                    'shop_product_id',
                    'created_by',
                ],
                'required',
            ],

            [['rate'], 'integer', 'min' => 1, 'max' => 5],

            [['message', 'seller_message', 'status'], 'string'],

            [['message', 'seller_message', 'seller_cms_user_id'], 'default', 'value' => null],

            [['shop_product_id', 'created_by'], 'unique', 'targetAttribute' => ['shop_product_id', 'created_by'], 'message' => "Вы уже оставляли отзыв к этому товару"],

            [['imageIds', 'fileIds'], 'safe'],

            [
                ['imageIds'],
                \skeeks\cms\validators\FileValidator::class,
                'skipOnEmpty' => false,
                'extensions'  => ['jpg', 'jpeg', 'gif', 'png'],
                'maxFiles'    => 5,
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
            'id'                 => Yii::t('skeeks/shop/app', 'ID'),
            'created_by'         => Yii::t('skeeks/shop/app', 'Пользователь'),
            'shop_product_id'    => Yii::t('skeeks/shop/app', 'Товар'),
            'status'             => Yii::t('skeeks/shop/app', 'Статус'),
            'message'            => Yii::t('skeeks/shop/app', 'О товаре'),
            'seller_message'     => Yii::t('skeeks/shop/app', 'Ответ представителя магазина'),
            'seller_cms_user_id' => Yii::t('skeeks/shop/app', 'Кто отвечал на отзыв'),
            'rate'               => Yii::t('skeeks/shop/app', 'Оценка'),
            'imageIds'           => Yii::t('skeeks/shop/app', 'Фото товара'),
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
        return $this->hasMany(StorageFile::class, ['id' => 'storage_file_id'])
            ->viaTable('shop_feedback2image', ['shop_feedback_id' => 'id'])
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
     * @return \yii\db\ActiveQuery
     */
    public function getShopProduct()
    {
        return $this->hasOne(ShopProduct::class, ['id' => 'shop_product_id']);
    }

}