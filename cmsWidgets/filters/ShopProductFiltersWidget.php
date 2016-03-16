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
use skeeks\cms\models\searchs\SearchChildrenRelatedPropertiesModel;
use skeeks\cms\models\Tree;
use skeeks\cms\shop\cmsWidgets\filters\models\SearchProductsModel;
use skeeks\cms\models\searchs\SearchRelatedPropertiesModel;
use skeeks\cms\shop\models\ShopContent;
use skeeks\cms\shop\models\ShopTypePrice;
use yii\base\DynamicModel;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\ActiveForm;

/**
 * @property ShopTypePrice      $typePrice;
 * @property CmsContent         $cmsContent;
 * @property ShopContent        $shopContent;
 * @property CmsContent         $offerCmsContent;
 * @property []                 $childrenElementIds;
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
    public $offerRelatedProperties      = [];

    public $type_price_id               = "";

    /**
     * @var array (Массив ids записей, для показа только нужных фильтров)
     */
    public $elementIds          = [];

    /**
     *
     * @var \skeeks\cms\shop\cmsWidgets\filters\models\SearchProductsModel
     */
    public $searchModel                 = null;


    /**
     * @var SearchRelatedPropertiesModel
     */
    public $searchRelatedPropertiesModel  = null;

    /**
     * @var SearchChildrenRelatedPropertiesModel
     */
    public $searchOfferRelatedPropertiesModel  = null;

    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name'          => 'Фильтры',
        ]);
    }

    public function init()
    {
        parent::init();

        if (!$this->searchModelAttributes)
        {
            $this->searchModelAttributes = [];
        }

        if (!$this->searchModel)
        {
            $this->searchModel = new \skeeks\cms\shop\cmsWidgets\filters\models\SearchProductsModel();
        }

        $this->searchModel->load(\Yii::$app->request->get());

        if ($this->cmsContent)
        {
            $this->searchRelatedPropertiesModel = new SearchRelatedPropertiesModel();
            $this->searchRelatedPropertiesModel->initCmsContent($this->cmsContent);
            $this->searchRelatedPropertiesModel->load(\Yii::$app->request->get());
        }

        if ($this->offerRelatedProperties && $this->cmsContent)
        {
            $this->searchOfferRelatedPropertiesModel = new SearchChildrenRelatedPropertiesModel();
            $this->searchOfferRelatedPropertiesModel->initCmsContent($this->offerCmsContent);
            $this->searchOfferRelatedPropertiesModel->load(\Yii::$app->request->get());
        }
    }




    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),
        [
            'content_id'                => \skeeks\cms\shop\Module::t('app', 'Content'),
            'searchModelAttributes'     => \skeeks\cms\shop\Module::t('app', 'Fields'),
            'realatedProperties'        => \skeeks\cms\shop\Module::t('app', 'Properties'),
            'offerRelatedProperties'    => \skeeks\cms\shop\Module::t('app', 'Offer properties'),
            'type_price_id'             => \skeeks\cms\shop\Module::t('app', 'Types of prices'),
        ]);
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(),
        [
            [['content_id'], 'integer'],
            [['searchModelAttributes'], 'safe'],
            [['offerRelatedProperties'], 'safe'],
            [['realatedProperties'], 'safe'],
            [['type_price_id'], 'integer'],
        ]);
    }

    public function renderConfigForm(ActiveForm $form)
    {
        echo \Yii::$app->view->renderFile(__DIR__ . '/_form.php', [
            'form'  => $form,
            'model' => $this
        ], $this);
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
    public function getOfferCmsContent()
    {
        return $this->shopContent->offerContent;
    }
    /**
     * @return CmsContent
     */
    public function getCmsContent()
    {
        return CmsContent::findOne($this->content_id);
    }

    /**
     * @return ShopContent
     */
    public function getShopContent()
    {
        return ShopContent::findOne(['content_id' => $this->content_id]);
    }

    /**
     * @return array
     */
    public function getChildrenElementIds()
    {
        return array_keys( CmsContentElement::find()->andWhere([
            'parent_content_element_id' => $this->elementIds
        ])->asArray()->indexBy('id')->all() );
    }

    /**
     * @param ActiveDataProvider $activeDataProvider
     */
    public function search(ActiveDataProvider $activeDataProvider)
    {
        $this->searchModel->search($activeDataProvider);

        if ($this->searchRelatedPropertiesModel)
        {
            $this->searchRelatedPropertiesModel->search($activeDataProvider);
        }

        if ($this->searchOfferRelatedPropertiesModel)
        {
            $this->searchOfferRelatedPropertiesModel->search($activeDataProvider);
        }
    }
}