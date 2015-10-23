<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 31.08.2015
 */
namespace skeeks\cms\shop\models;

use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsSite;
use skeeks\cms\models\Core;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shop_tax}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $description
 * @property string $code
 * @property string $site_code
 *
 * @property CmsSite $site
 */
class ShopTax extends Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_tax}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at'], 'integer'],
            [['name', 'code'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 50],
            [['site_code'], 'string', 'max' => 15]
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
            'description'   => \skeeks\cms\shop\Module::t('app', 'Description'),
            'code'          => \skeeks\cms\shop\Module::t('app', 'Code'),
            'site_code'     => \skeeks\cms\shop\Module::t('app', 'Site'),
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(CmsSite::className(), ['code' => 'site_code']);
    }
}