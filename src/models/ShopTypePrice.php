<?php

namespace skeeks\cms\shop\models;

use skeeks\cms\behaviors\RelationalBehavior;
use skeeks\cms\rbac\models\CmsAuthItem;
use yii\base\Event;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_type_price}}".
 *
 * @property integer      $id
 * @property integer      $created_by
 * @property integer      $updated_by
 * @property integer      $created_at
 * @property integer      $updated_at
 * @property string|null  $external_id
 * @property string       $name
 * @property string       $description
 * @property integer      $priority
 * @property integer|null $cms_site_id
 * @property integer|null $is_default
 *
 * @property CmsAuthItem[]            $cmsUserRoles
 * @property CmsAuthItem[]            $viewCmsUserRoles
 *
 * ***
 *
 * @property string       $buyPermissionName
 * @property string       $viewPermissionName
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

    public function behaviors()
    {
        return [
            RelationalBehavior::class,
        ];
    }

    public function init()
    {
        parent::init();

        $this->on(self::EVENT_BEFORE_INSERT, [$this, 'beforeInsertChecks']);
        $this->on(self::EVENT_BEFORE_UPDATE, [$this, 'beforeUpdateChecks']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['priority'], 'integer'],
            [['cms_site_id'], 'integer'],
            [['name'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
            ['is_default', 'default', 'value' => null],

            [['external_id'], 'default', 'value' => null],
            //[['external_id', 'shop_supplier_id'], 'unique', 'targetAttribute' => ['external_id', 'shop_supplier_id']],
            [['external_id'], 'string'],

            [
                'cms_site_id',
                'default',
                'value' => function () {
                    if (\Yii::$app->skeeks->site) {
                        return \Yii::$app->skeeks->site->id;
                    }
                },
            ],

            ['cmsUserRoles', 'safe'], // allow set permissions with setAttributes()
            ['viewCmsUserRoles', 'safe'], // allow set permissions with setAttributes()

            [
                ['cms_site_id', 'external_id'],
                'unique',
                'targetAttribute' => ['cms_site_id', 'external_id'],
                'when'            => function (ShopTypePrice $model) {
                    return (bool)$model->external_id;
                },
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'name'             => \Yii::t('skeeks/shop/app', 'Name'),
            'description'      => \Yii::t('skeeks/shop/app', 'Description'),
            'priority'         => \Yii::t('skeeks/shop/app', 'Priority'),
            'external_id'      => "ID из внешней системы",
            'cms_site_id'      => "Сайт",
            'is_default'      => "Главная цена",
            'cmsUserRoles'    => \Yii::t('skeeks/shop/app', 'Кто может покупать по этой цене?'),
            'viewCmsUserRoles'    => \Yii::t('skeeks/shop/app', 'Кто может видеть эту цену?'),
        ]);
    }
    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return array_merge(parent::attributeHints(), [
            'is_default'      => "Обычно это розничная цена доступная всем покупателям",
            'cmsUserRoles'      => "Если ничего не выбрано, то могут все.",
            'viewCmsUserRoles'      => "Если ничего не выбрано, то видят все клиенты.",
        ]);
    }


    public function getCmsUserRoles()
    {
        return $this->hasMany(CmsAuthItem::class, ['name' => 'auth_item_name'])
            ->viaTable('{{%shop_type_price2auth_item}}', ['shop_type_price_id' => 'id']);
    }

    public function getViewCmsUserRoles()
    {
        return $this->hasMany(CmsAuthItem::class, ['name' => 'auth_item_name'])
            ->viaTable('{{%shop_type_price2view_auth_item}}', ['shop_type_price_id' => 'id']);
    }


    /**
     * @return string
     */
    public function getViewPermissionName()
    {
        return "view-shop-type-price-".$this->id;
    }


    /**
     * @return bool
     * @deprecated
     */
    public function getIsDefault()
    {
        if (!\Yii::$app->shop->baseTypePrice) {
            return false;
        }
        return (bool)($this->id == \Yii::$app->shop->baseTypePrice->id);
    }


    /**
     * @param Event $e
     * @throws Exception
     */
    public function beforeUpdateChecks(Event $e)
    {
        //Если этот элемент по умолчанию выбран, то все остальны нужно сбросить.
        if ($this->is_default) {

            static::updateAll(
            [
                'is_default' => null,
            ],
            [
                "and",
                ['!=', 'id', $this->id],
                ['cms_site_id' => $this->cms_site_id]
            ]);
            $this->is_default = 1; //сайт по умолчанию всегда активный
        }
    }

    /**
     * @param Event $e
     * @throws Exception
     */
    public function beforeInsertChecks(Event $e)
    {
        //Если этот элемент по умолчанию выбран, то все остальны нужно сбросить.
        if ($this->is_default) {

            static::updateAll([
                'is_default' => null,
            ],
                ['cms_site_id' => $this->cms_site_id]
            );

            $this->is_default = 1; //сайт по умолчанию всегда активный
        }
    }
}