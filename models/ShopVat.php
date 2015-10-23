<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 31.08.2015
 */
namespace skeeks\cms\shop\models;

use skeeks\cms\components\Cms;
use skeeks\cms\models\Core;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_vat}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property integer $priority
 * @property string $active
 * @property string $rate
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
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'priority'], 'integer'],
            [['name'], 'required'],
            [['rate'], 'number'],
            [['name'], 'string', 'max' => 255],
            [['active'], 'string', 'max' => 1],
            [['active'], 'default', 'value' => Cms::BOOL_Y],
            [['active'], 'in', 'range' => array_keys(\Yii::$app->cms->booleanFormat())],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'id'            => \skeeks\cms\shop\Module::t('app', 'ID'),
            'created_by'    => \skeeks\cms\shop\Module::t('app', 'Created By'),
            'updated_by'    => \skeeks\cms\shop\Module::t('app', 'Updated By'),
            'created_at'    => \skeeks\cms\shop\Module::t('app', 'Created At'),
            'updated_at'    => \skeeks\cms\shop\Module::t('app', 'Updated At'),
            'name'          => \skeeks\cms\shop\Module::t('app', 'Name'),
            'priority'      => \skeeks\cms\shop\Module::t('app', 'Priority'),
            'active'        => \skeeks\cms\shop\Module::t('app', 'Active'),
            'rate'          => \skeeks\cms\shop\Module::t('app', 'Bet'),
        ]);
    }
}