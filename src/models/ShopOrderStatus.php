<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\models\Core;
use yii\base\UserException;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_order_status}}".
 *
 * @property string      $name
 * @property string      $description
 * @property integer     $priority
 * @property string      $color
 *
 * @property ShopOrder[] $shopOrders
 */
class ShopOrderStatus extends Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_order_status}}';
    }
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        //$this->on(BaseActiveRecord::EVENT_BEFORE_DELETE, [$this, "checkDelete"]);
    }
    public function checkDelete()
    {
        if ($this->isProtected()) {
            throw new UserException(\Yii::t('skeeks/shop/app', 'You can not remove this status'));
        }
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), []);
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'name'        => \Yii::t('skeeks/shop/app', 'Name'),
            'description' => \Yii::t('skeeks/shop/app', 'Description'),
            'priority'    => \Yii::t('skeeks/shop/app', 'Priority'),
            'color'       => \Yii::t('skeeks/shop/app', 'Color'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['priority'], 'integer'],
            [['name'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['color'], 'string', 'max' => 32],
        ]);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrders()
    {
        return $this->hasMany(ShopOrder::class, ['shop_order_status_id' => 'id']);
    }
}