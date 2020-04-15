<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsSite;
use yii\helpers\ArrayHelper;

/**
 * @property string  $description
 * @property string  $description_internal
 * @property int     $id
 * @property int     $is_supplier
 *
 * @property CmsSite $cmsSite
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopSite extends \skeeks\cms\base\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shop_site}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['is_supplier'], 'integer'],

            [['description'], 'string'],
            [['description_internal'], 'string'],

            [
                'id',
                'default',
                'value' => function () {
                    if (\Yii::$app->cms->site) {
                        return \Yii::$app->cms->site->id;
                    }
                },
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [

            'description'          => "Описание",
            'description_internal' => "Скрытое описание",
            'is_supplier'          => "Поставщик товаров?",
        ]);
    }

    /**
     * @return array
     */
    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [

            'is_supplier'          => "Этот сайт является поставщиком товаров для других сайтов?",
        ]);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsSite()
    {
        return $this->hasOne(CmsSite::class, ['id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopSupplier()
    {
        return $this->hasOne(ShopSupplier::class, ['id' => 'shop_supplier_id']);
    }
}