<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 31.08.2015
 */
namespace skeeks\cms\shop\models;

use skeeks\cms\models\Core;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_pay_system}}".
 *
 * @property string $name
 * @property integer $priority
 * @property string $active
 * @property string $description
 *
 * @property ShopPaySystemPersonType[] $shopPaySystemPersonTypes
 * @property ShopPersonType[] $personTypes
 */
class ShopPaySystem extends Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_pay_system}}';
    }

    protected $_personTypes = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->on(self::EVENT_AFTER_INSERT,    [$this, "afterSaveEvent"]);
        $this->on(self::EVENT_AFTER_UPDATE,    [$this, "afterSaveEvent"]);
    }

    /**
     * @param $event
     */
    public function afterSaveEvent($event)
    {
        if ($this->_personTypes)
        {
            //Для начала удаляем текущие связи
            $all = $this->getShopPaySystemPersonTypes()->all();
            if ($all)
            {
                foreach ($all as $one)
                {
                    $one->delete();
                }
            }

            //добавляем новые
            foreach ($this->_personTypes as $id)
            {
                $shopTypeSite = $this->getShopPaySystemPersonTypes()->andWhere(['person_type_id' => $id])->one();
                //Такой связи еще нет
                if (!$shopTypeSite)
                {
                    $shopTypeSite                   = new ShopPaySystemPersonType();

                    $shopTypeSite->pay_system_id    = $this->id;
                    $shopTypeSite->person_type_id   = $id;

                    $shopTypeSite->save();
                }
            }

        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'priority'], 'integer'],
            [['name'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['active'], 'string', 'max' => 1],
            [['name'], 'unique'],
            [['personTypeIds'], 'safe']
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'name' => Yii::t('app', 'Name'),
            'priority' => Yii::t('app', 'Priority'),
            'active' => Yii::t('app', 'Active'),
            'description' => Yii::t('app', 'Description'),
            'personTypeIds' => Yii::t('app', 'Плательщики'),
        ]);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopPaySystemPersonTypes()
    {
        return $this->hasMany(ShopPaySystemPersonType::className(), ['pay_system_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPersonTypes()
    {
        return $this->hasMany(ShopPersonType::className(), ['id' => 'person_type_id'])->viaTable('{{%shop_pay_system_person_type}}', ['pay_system_id' => 'id']);
    }




    /**
     * @return int[]
     */
    public function getPersonTypeIds()
    {
        return (array) ArrayHelper::map($this->personTypes, 'id', 'id');
    }


    /**
     * @param array $codes
     * @return $this
     */
    public function setPersonTypeIds($ids = [])
    {
        $this->_personTypes = $ids;
        return $this;
    }
}