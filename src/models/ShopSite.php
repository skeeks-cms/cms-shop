<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsSite;
use skeeks\cms\models\CmsTree;
use yii\helpers\ArrayHelper;

/**
 * @property string   $description
 * @property string   $description_internal
 * @property int      $id
 * @property int      $is_supplier
 * @property int      $is_receiver
 * @property int|null $catalog_cms_tree_id
 *
 * @property CmsSite  $cmsSite
 * @property CmsTree  $catalogCmsTree
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopSite extends \skeeks\cms\base\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%shop_site}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['is_supplier'], 'integer'],
            [['is_receiver'], 'integer'],

            [['catalog_cms_tree_id'], 'integer'],
            [['catalog_cms_tree_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsTree::className(), 'targetAttribute' => ['catalog_cms_tree_id' => 'id']],

            [['description'], 'string'],
            [['description_internal'], 'string'],

            [
                'id',
                'default',
                'value' => function () {
                    if (\Yii::$app->skeeks->site) {
                        return \Yii::$app->skeeks->site->id;
                    }
                },
            ],

            [
                'catalog_cms_tree_id',
                function () {
                    if ($this->catalog_cms_tree_id) {
                        if ($this->cmsSite->id != $this->catalogCmsTree->cms_site_id) {
                            $this->addError("catalog_cms_tree_id", "Раздел каталога должен лежать в этом же сайте");
                        }
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

            'description'          => "Описание",
            'description_internal' => "Скрытое описание",
            'is_supplier'          => "Поставщик товаров?",
            'is_receiver'          => "Разрешено получать товары от постащиков",
            'catalog_cms_tree_id'  => "Основной раздел для товаров",
        ]);
    }

    /**
     * @return array
     */
    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
            'is_supplier' => "Этот сайт является поставщиком товаров для других сайтов. Значит товары на этом сайте необходимо привязывать к главным товарам портала.",
            'is_receiver' => "Если эта опция включена то на сайте появляется раздел «Поставщики»",
            'catalog_cms_tree_id' => "",
        ]);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsSite()
    {
        $class = \Yii::$app->skeeks->siteClass;
        return $this->hasOne($class, ['id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopSupplier()
    {
        return $this->hasOne(ShopSupplier::class, ['id' => 'shop_supplier_id']);
    }

    /**
     * @return string
     */
    public function asText()
    {
        return $this->cmsSite->asText;
    }

    /**
     * Gets query for [[CatalogCmsTree]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCatalogCmsTree()
    {
        return $this->hasOne(CmsTree::className(), ['id' => 'catalog_cms_tree_id']);
    }
}