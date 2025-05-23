<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\base\ActiveRecord;
use skeeks\cms\behaviors\CmsLogBehavior;
use skeeks\cms\models\behaviors\traits\HasLogTrait;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "shop_bonus_transaction".
 *
 * @property int         $id
 * @property int|null    $created_at
 * @property int|null    $updated_at
 * @property int|null    $created_by
 * @property int|null    $updated_by
 * @property int         $cms_site_id Сайт
 * @property int|null    $cms_user_id Пользователь
 * @property int|null    $shop_order_id Заказ
 * @property int         $is_debit Дебет? (иначе кредит)
 * @property float       $value Количество бонусов
 * @property int|null    $end_at Дата до которой действуют бонусы
 * @property string|null $comment Комментарий
 *
 * @property CmsSite     $cmsSite
 * @property CmsUser     $cmsUser
 * @property ShopOrder   $shopOrder
 */
class ShopBonusTransaction extends ActiveRecord
{
    use HasLogTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'shop_bonus_transaction';
    }

    public function init()
    {
        $this->on(self::EVENT_AFTER_FIND, function() {
            $this->value = (float) $this->value;
        });

        return parent::init();
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            CmsLogBehavior::class     => [
                'class' => CmsLogBehavior::class,
                'relation_map' => [
                    'cms_user_id' => 'cmsUser',
                    'shop_order_id' => 'shopOrder',
                ],
            ],
        ]);
    }

    /**
     * @return string
     */
    public function asText()
    {
        if ($this->is_debit) {
            return "Списание с клиента №" . $this->id;
        } else {
            return "Начисление клиенту №" . $this->id;
        }
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_at', 'updated_at', 'created_by', 'updated_by', 'cms_site_id', 'cms_user_id', 'shop_order_id', 'is_debit', 'end_at'], 'integer'],
            [['value'], 'number'],
            [['comment'], 'string'],
            [['cms_site_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsSite::class, 'targetAttribute' => ['cms_site_id' => 'id']],
            [['cms_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => \Yii::$app->user->identityClass, 'targetAttribute' => ['cms_user_id' => 'id']],
            [['shop_order_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopOrder::class, 'targetAttribute' => ['shop_order_id' => 'id']],

            [['value'], function($attribute) {
                if ($this->value <= 0) {
                    $this->addError($attribute, "Необходимо указать больше 0");
                    return false;
                }
            }],

            [
                'cms_site_id',
                'default',
                'value' => function () {
                    if (\Yii::$app->skeeks->site) {
                        return \Yii::$app->skeeks->site->id;
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
            'cms_user_id'   => 'Пользователь',
            'shop_order_id' => 'Связь с продажей/заказом',
            'is_debit'      => 'Тип операции',
            'value'         => 'Количество бонусов',
            'end_at'        => 'Дата до которой действуют бонусы',
            'comment'       => 'Комментарий',
        ]);
    }

    /**
     * Gets query for [[CmsSite]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCmsSite()
    {
        $class = \Yii::$app->skeeks->siteClass;
        return $this->hasOne($class, ['id' => 'cms_site_id']);
    }

    /**
     * Gets query for [[CmsUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCmsUser()
    {
        return $this->hasOne(\Yii::$app->user->identityClass, ['id' => 'cms_user_id']);
    }

    /**
     * Gets query for [[ShopOrder]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrder()
    {
        return $this->hasOne(ShopOrder::class, ['id' => 'shop_order_id']);
    }
}