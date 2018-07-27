<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 31.08.2015
 */

namespace skeeks\cms\shop\models;

/**
 * This is the model class for table "{{%shop_pay_system_person_type}}".
 *
 * @property integer        $pay_system_id
 * @property integer        $person_type_id
 *
 * @property ShopPaySystem  $paySystem
 * @property ShopPersonType $personType
 */
class ShopPaySystemPersonType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_pay_system_person_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pay_system_id', 'person_type_id'], 'required'],
            [['pay_system_id', 'person_type_id'], 'integer'],
            [
                ['pay_system_id', 'person_type_id'],
                'unique',
                'targetAttribute' => ['pay_system_id', 'person_type_id'],
                'message'         => 'The combination of Pay System ID and Person Type ID has already been taken.',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'pay_system_id'  => \Yii::t('skeeks/shop/app', 'ID pay system'),
            'person_type_id' => \Yii::t('skeeks/shop/app', 'ID of person'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaySystem()
    {
        return $this->hasOne(ShopPaySystem::className(), ['id' => 'pay_system_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPersonType()
    {
        return $this->hasOne(ShopPersonType::className(), ['id' => 'person_type_id']);
    }
}