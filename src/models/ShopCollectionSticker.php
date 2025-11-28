<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 14.09.2015
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\base\ActiveRecord;
use skeeks\modules\cms\money\models\Currency;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * @property integer          $id
 * @property integer|null     $created_by
 * @property integer|null     $created_at
 *
 * @property string           $name
 * @property string           $color
 * @property string|null      $description
 * @property integer          $priority
 *
 *
 * @property ShopCollection[] $shopCollections
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopCollectionSticker extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_collection_sticker}}';
    }


    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [

        ]);
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
                    'priority',
                ],
                'integer',
            ],

            [
                [
                    'name',
                    'color',
                    'description',
                ],
                'string',
            ],
            //Обязательные поля
            [
                [
                    'name',
                    'color',
                ],
                'required',
            ],
            //Фильтры и обработчики
            [
                [
                    'name',
                    'color',
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


        ]);
    }


    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getShopCollections()
    {
        return $this->hasMany(ShopCollection::class, ['id' => 'shop_collection_id'])
            ->viaTable('shop_collection2sticker', ['shop_collection_sticker_id' => 'id']);
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id'          => Yii::t('skeeks/shop/app', 'ID'),
            'name'        => Yii::t('skeeks/shop/app', 'Название'),
            'description' => Yii::t('skeeks/shop/app', 'Описание'),
            'color' => Yii::t('skeeks/shop/app', 'Цвет'),
            'priority'    => Yii::t('skeeks/cms', 'Сортировка'),
        ]);
    }
    /**
     * {@inheritdoc}
     */
    /*public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
            'name'        => Yii::t('skeeks/shop/app', 'Название бренда, в точности как оно зарегистрировано!'),
            'external_id' => Yii::t('skeeks/shop/app', 'Используется при интеграции со сторонними системами'),
        ]);
    }*/


}