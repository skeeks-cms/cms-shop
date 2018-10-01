<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\components\Cms;
use yii\base\Event;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_type_price}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property string  $code
 * @property string  $name
 * @property string  $description
 * @property integer $priority
 * @property string  $def
 * @property string  $xml_id
 *
 * ***
 *
 * @property string  $isDefault
 * @property string  $buyPermissionName
 * @property string  $viewPermissionName
 */
class ShopTypePrice extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_type_price}}';
    }

    //TODO: надо вынести в трейт
    public function init()
    {
        parent::init();

        $this->on(self::EVENT_BEFORE_INSERT, [$this, 'beforeInsertChecks']);
        $this->on(self::EVENT_BEFORE_UPDATE, [$this, 'beforeUpdateChecks']);

    }

    /**
     * @param Event $e
     * @throws Exception
     */
    public function beforeUpdateChecks(Event $e)
    {
        //Если этот элемент по умолчанию выбран, то все остальны нужно сбросить.
        if ($this->def == Cms::BOOL_Y) {
            static::updateAll(
                [
                    'def' => Cms::BOOL_N,
                ],
                ['!=', 'id', $this->id]
            );
        }

    }

    /**
     * @param Event $e
     * @throws Exception
     */
    public function beforeInsertChecks(Event $e)
    {
        //Если этот элемент по умолчанию выбран, то все остальны нужно сбросить.
        if ($this->def == Cms::BOOL_Y) {
            static::updateAll([
                'def' => Cms::BOOL_N,
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['priority'], 'integer'],
            [['code', 'name'], 'required'],
            [['description'], 'string'],
            [['code'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 255],
            [['xml_id'], 'string', 'max' => 255],
            [['def'], 'string', 'max' => 1],
            [['code'], 'unique'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'code'        => \Yii::t('skeeks/shop/app', 'Code'),
            'name'        => \Yii::t('skeeks/shop/app', 'Name'),
            'description' => \Yii::t('skeeks/shop/app', 'Description'),
            'priority'    => \Yii::t('skeeks/shop/app', 'Priority'),
            'def'         => \Yii::t('skeeks/shop/app', 'Default'),
        ]);
    }


    /**
     * @return string
     */
    public function getViewPermissionName()
    {
        return "view-shop-type-price-".$this->id;
    }

    /**
     * @return string
     */
    public function getBuyPermissionName()
    {
        return "view-shop-type-price-".$this->id;
    }

    /**
     * @return bool
     */
    public function getIsDefault()
    {
        return (bool)($this->def == 'Y');
    }
}