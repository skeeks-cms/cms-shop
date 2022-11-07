<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsSite;
use skeeks\cms\models\CmsUser;
use skeeks\cms\money\Money;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "shop_cashebox2user".
 *
 * @property int $id
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int $cms_user_id Сотрудник
 * @property int $shop_cashebox_id Касса
 * @property int $is_active Активен?
 * @property string|null $cashiers_name Имя кассира на чеке
 * @property string|null $comment Комментарий
 * @property string $cashiersName 
 *
 * @property CmsUser $cmsUser
 * @property ShopCashebox $shopCashebox
 */
class ShopCashebox2user extends \skeeks\cms\base\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shop_cashebox2user}}';
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [

            [['created_at', 'created_by', 'cms_user_id', 'shop_cashebox_id', 'is_active'], 'integer'],
            [['comment'], 'string'],
            [['shop_cashebox_id'], 'required'],
            [['cms_user_id'], 'required'],
            [['cashiers_name'], 'string', 'max' => 255],
            [['cms_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::class, 'targetAttribute' => ['cms_user_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::class, 'targetAttribute' => ['created_by' => 'id']],
            [['shop_cashebox_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopCashebox::class, 'targetAttribute' => ['shop_cashebox_id' => 'id']],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'cms_user_id' => 'Сотрудник',
            'shop_cashebox_id' => 'Касса',
            'is_active' => 'Активен?',
            'cashiers_name' => 'Имя кассира на чеке',
            'comment' => 'Комментарий',
        ]);
    }
    /**
     * {@inheritdoc}
     */
    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'cashiers_name' => 'Именна эта информация будет попадать в чек и в налоговую. Если это поле не будет заполнено то имя и фамилия возьмутся из данных сотрудника.',
        ]);
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
     * Gets query for [[ShopCashebox]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopCashebox()
    {
        return $this->hasOne(ShopCashebox::class, ['id' => 'shop_cashebox_id']);
    }

    /**
     * @return string
     */
    public function getCashiersName()
    {
        return $this->cashiers_name ? $this->cashiers_name : $this->cmsUser->shortDisplayName;
    }
}