<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 31.08.2015
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\models\Core;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_vat}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property string  $name
 * @property integer $priority
 * @property integer $is_active
 * @property number  $rate
 */
class ShopVat extends Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_vat}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'priority', 'is_active'], 'integer'],
            [['name'], 'required'],
            [['rate'], 'number'],
            [['name'], 'string', 'max' => 255],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'id'         => \Yii::t('skeeks/shop/app', 'ID'),
            'created_by' => \Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by' => \Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at' => \Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at' => \Yii::t('skeeks/shop/app', 'Updated At'),
            'name'       => \Yii::t('skeeks/shop/app', 'Name'),
            'priority'   => \Yii::t('skeeks/shop/app', 'Priority'),
            'is_active'  => \Yii::t('skeeks/shop/app', 'Active'),
            'rate'       => \Yii::t('skeeks/shop/app', 'Bet'),
        ]);
    }
}