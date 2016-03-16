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
use skeeks\cms\models\CmsContentProperty;
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

    /**
     * @return \skeeks\cms\shop\models\ShopProductPrice
     */
    public function getMinPrice()
    {
        $minPrice = null;

        if ($this->elementIds && $this->typePrice) {
            $minPrice = \skeeks\cms\shop\models\ShopProductPrice::find()
                ->andWhere(['product_id' => $this->elementIds])
                ->andWhere(['type_price_id' => $this->typePrice->id])
                ->orderBy(['price' => SORT_ASC])
                ->one();
        }

        return $minPrice;
    }

    /**
     * @return \skeeks\cms\shop\models\ShopProductPrice
     */
    public function getMaxPrice()
    {
        $maxPrice = null;

        if ($this->elementIds && $this->typePrice) {
            $maxPrice = \skeeks\cms\shop\models\ShopProductPrice::find()
                ->andWhere(['product_id' => $this->elementIds])
                ->andWhere(['type_price_id' => $this->typePrice->id])
                ->orderBy(['price' => SORT_DESC])
                ->one();
        }

        return $maxPrice;
    }

    protected function _run()
    {

        if ($this->elementIds && !$this->searchModel->price_from && $this->typePrice)
        {
            $this->searchModel->price_from = $this->minPrice->price;
        }

        if ($this->elementIds && !$this->searchModel->price_to && $this->typePrice)
        {
            $this->searchModel->price_to = $this->maxPrice->price;
        }

        return parent::_run();
    }


    /**
     *
     * Получение доступных опций для свойства
     * @param CmsContentProperty $property
     * @return $this|array|\yii\db\ActiveRecord[]
     */
    public function getRelatedPropertyOptions($property)
    {
        $options = [];

        if ($property->property_type == \skeeks\cms\relatedProperties\PropertyType::CODE_ELEMENT)
        {
            $propertyType = $property->createPropertyType();

            if ($this->elementIds)
            {
                $availables = \skeeks\cms\models\CmsContentElementProperty::find()
                    ->select(['value_enum'])
                    ->indexBy('value_enum')
                    ->andWhere(['element_id' => $this->elementIds])
                    ->andWhere(['property_id' => $property->id])
                    ->asArray()
                    ->all()
                ;

                $availables = array_keys($availables);
            }

            $options = \skeeks\cms\models\CmsContentElement::find()
                ->active()
                ->andWhere(['content_id' => $propertyType->content_id]);
                if ($this->elementIds)
                {
                    $options->andWhere(['id' => $availables]);
                }

            $options = $options->select(['id', 'name'])->asArray()->all();

            $options = \yii\helpers\ArrayHelper::map(
                $options, 'id', 'name'
            );

        } elseif ($property->property_type == \skeeks\cms\relatedProperties\PropertyType::CODE_LIST)
        {
            $options = $property->getEnums()->select(['id', 'value']);

            if ($this->elementIds)
            {
                $availables = \skeeks\cms\models\CmsContentElementProperty::find()
                    ->select(['value_enum'])
                    ->indexBy('value_enum')
                    ->andWhere(['element_id' => $this->elementIds])
                    ->andWhere(['property_id' => $property->id])
                    ->asArray()
                    ->all()
                ;

                $availables = array_keys($availables);
                $options->andWhere(['id' => $availables]);
            }

            $options = $options->asArray()->all();

            $options = \yii\helpers\ArrayHelper::map(
                $options, 'id', 'value'
            );
        }

        return $options;
    }


    /**
     *
     * Получение доступных опций для свойства
     * @param CmsContentProperty $property
     * @return $this|array|\yii\db\ActiveRecord[]
     */
    public function getOfferRelatedPropertyOptions($property)
    {
        if ($property->property_type == \skeeks\cms\relatedProperties\PropertyType::CODE_ELEMENT)
        {
            $propertyType = $property->createPropertyType();

            if ($this->childrenElementIds)
            {
                $availables = \skeeks\cms\models\CmsContentElementProperty::find()
                    ->select(['value_enum'])
                    ->indexBy('value_enum')
                    ->andWhere(['element_id' => $this->childrenElementIds])
                    ->andWhere(['property_id' => $property->id])
                    ->asArray()
                    ->all()
                ;

                $availables = array_keys($availables);
            }

            $options = \skeeks\cms\models\CmsContentElement::find()
                ->active()
                ->andWhere(['content_id' => $propertyType->content_id]);
                if ($this->childrenElementIds)
                {
                    $options->andWhere(['id' => $availables]);
                }

            $options = $options->select(['id', 'name'])->asArray()->all();

            $options = \yii\helpers\ArrayHelper::map(
                $options, 'id', 'name'
            );
        } elseif ($property->property_type == \skeeks\cms\relatedProperties\PropertyType::CODE_LIST)
        {
            $options = $property->getEnums()->select(['id', 'value']);

            if ($this->childrenElementIds)
            {
                $availables = \skeeks\cms\models\CmsContentElementProperty::find()
                    ->select(['value_enum'])
                    ->indexBy('value_enum')
                    ->andWhere(['element_id' => $this->childrenElementIds])
                    ->andWhere(['property_id' => $property->id])
                    ->asArray()
                    ->all()
                ;

                $availables = array_keys($availables);
                $options->andWhere(['id' => $availables]);
            }

            $options = $options->asArray()->all();

            $options = \yii\helpers\ArrayHelper::map(
                $options, 'id', 'value'
            );
        }

        return $options;
    }
}