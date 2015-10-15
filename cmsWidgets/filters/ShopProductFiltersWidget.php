<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 25.05.2015
 */
namespace skeeks\cms\shop\cmsWidgets\filters;

use skeeks\cms\base\Widget;
use skeeks\cms\base\WidgetRenderable;
use skeeks\cms\components\Cms;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsContentElementTree;
use skeeks\cms\models\Search;
use skeeks\cms\models\Tree;
use skeeks\cms\shop\cmsWidgets\filters\models\SearchProductsModel;
use skeeks\cms\shop\cmsWidgets\filters\models\SearchRelatedPropertiesModel;
use skeeks\cms\shop\models\ShopTypePrice;
use yii\base\DynamicModel;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * @property ShopTypePrice      $typePrice;
 * @property CmsContent         $cmsContent;
 *
 * Class ShopProductFiltersWidget
 * @package skeeks\cms\shop\cmsWidgets\filters
 */
class ShopProductFiltersWidget extends WidgetRenderable
{
    //Навигация
    public $content_id;
    public $searchModelAttributes       = [];

    public $realatedProperties          = [];
    public $type_price_id               = "";

    /**
     * @var \skeeks\cms\shop\cmsWidgets\filters\models\SearchProductsModel
     */
    public $searchModel                 = null;


    /**
     * @var SearchRelatedPropertiesModel
     */
    public $searchRelatedPropertiesModel  = null;

    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name'          => 'Фильтры',
        ]);
    }

    public function init()
    {
        parent::init();

        if (!$this->searchModel)
        {
            $this->searchModel = new \skeeks\cms\shop\cmsWidgets\filters\models\SearchProductsModel();
        }

        if (!$this->searchRelatedPropertiesModel)
        {
            $this->searchRelatedPropertiesModel = new SearchRelatedPropertiesModel();
            $this->searchRelatedPropertiesModel->initCmsContent($this->cmsContent);
        }

        $this->searchModel->load(\Yii::$app->request->get());
        $this->searchRelatedPropertiesModel->load(\Yii::$app->request->get());
    }




    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),
        [
            'content_id'                => 'Контент',
            'searchModelAttributes'     => 'Поля',
            'realatedProperties'        => 'Свойства',
            'type_price_id'             => 'Типы цен'
        ]);
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(),
        [
            [['content_id'], 'integer'],
            [['searchModelAttributes'], 'safe'],
            [['realatedProperties'], 'safe'],
            [['type_price_id'], 'integer'],
        ]);
    }

    /**
     * @return ShopTypePrice
     */
    public function getTypePrice()
    {
        if (!$this->type_price_id)
        {
            return null;
        }
        return ShopTypePrice::find()->where(['id' => $this->type_price_id])->one();
    }

    /**
     * @return CmsContent
     */
    public function getCmsContent()
    {
        return CmsContent::findOne($this->content_id);
    }
}