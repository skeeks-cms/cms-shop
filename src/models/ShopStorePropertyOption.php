<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\base\ActiveRecord;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsContentPropertyEnum;
use skeeks\cms\models\CmsTree;
use yii\helpers\ArrayHelper;
/**
 *
 * This is the model class for table "shop_supplier_property_option".
 *
 * @property int                    $id
 * @property int                    $shop_store_property_id
 * @property string                 $name
 * @property int|null               $cms_content_property_enum_id
 * @property int|null               $cms_content_element_id
 * @property int|null               $cms_tree_id
 * @property int|null               $shop_brand_id
 *
 * @property CmsTree                $cmsTree
 * @property ShopBrand              $shopBrand
 * @property CmsContentElement      $cmsContentElement
 * @property CmsContentPropertyEnum $cmsContentPropertyEnum
 * @property ShopStoreProperty      $shopStoreProperty
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopStorePropertyOption extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_store_property_option}}';
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [

            [['shop_store_property_id', 'name'], 'required'],
            [['shop_store_property_id', 'cms_content_property_enum_id', 'cms_content_element_id', 'cms_tree_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['shop_store_property_id', 'name'], 'unique', 'targetAttribute' => ['shop_store_property_id', 'name']],
            [['cms_content_element_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentElement::className(), 'targetAttribute' => ['cms_content_element_id' => 'id']],
            [['cms_content_property_enum_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentPropertyEnum::className(), 'targetAttribute' => ['cms_content_property_enum_id' => 'id']],
            //[['shop_brand_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentPropertyEnum::className(), 'targetAttribute' => ['cms_content_property_enum_id' => 'id']],

        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id'                           => 'ID',
            'shop_store_property_id'       => 'Характеристика',
            'name'                         => 'Название у поставщика',
            'cms_content_property_enum_id' => 'Опция на сайте',
            'cms_content_element_id'       => 'Опция на сайте',
            'shop_brand_id'                => 'Бренд на сайте',
            'cms_tree_id'                  => 'Раздел на сайте',
        ]);
    }

    /**
     * Gets query for [[CmsContentElement]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCmsContentElement()
    {
        return $this->hasOne(CmsContentElement::className(), ['id' => 'cms_content_element_id']);
    }
    /**
     * Gets query for [[CmsContentElement]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCmsTree()
    {
        return $this->hasOne(CmsTree::className(), ['id' => 'cms_tree_id']);
    }
    /**
     * Gets query for [[CmsContentElement]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopBrand()
    {
        return $this->hasOne(ShopBrand::className(), ['id' => 'shop_brand_id']);
    }

    /**
     * Gets query for [[CmsContentPropertyEnum]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCmsContentPropertyEnum()
    {
        return $this->hasOne(CmsContentPropertyEnum::className(), ['id' => 'cms_content_property_enum_id']);
    }

    /**
     * Gets query for [[ShopSupplierProperty]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopStoreProperty()
    {
        return $this->hasOne(ShopStoreProperty::className(), ['id' => 'shop_store_property_id']);
    }
}