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
     * @var bool Учитывать только доступные фильтры для текущей выборки
     */
    public $onlyExistsFilters           = false;

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
            'name'          => \Yii::t('skeeks/shop/app', 'Filters'),
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
            'content_id'                => \Yii::t('skeeks/shop/app', 'Content'),
            'searchModelAttributes'     => \Yii::t('skeeks/shop/app', 'Fields'),
            'realatedProperties'        => \Yii::t('skeeks/shop/app', 'Properties'),
            'offerRelatedProperties'    => \Yii::t('skeeks/shop/app', 'Offer properties'),
            'type_price_id'             => \Yii::t('skeeks/shop/app', 'Types of prices'),
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

    /**
     * @param ActiveDataProvider $activeDataProvider
     */
    public function search(ActiveDataProvider $activeDataProvider)
    {
        if ($this->onlyExistsFilters)
        {
            /**
             * @var $query \yii\db\ActiveQuery
             */
            $query  = clone $activeDataProvider->query;
            $ids    = $query->select(['cms_content_element.id as mainId'])->indexBy('mainId')->asArray()->all();

            $this->elementIds = array_keys($ids);
        }

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
     * @param $property
     * @return bool
     */
    public function isShowRelatedProperty($property)
    {
        if (!in_array($property->code, $this->realatedProperties))
        {
            return false;
        }

        if ($this->onlyExistsFilters === false)
        {
            return true;
        }

        if (in_array($property->property_type, [\skeeks\cms\relatedProperties\PropertyType::CODE_ELEMENT, \skeeks\cms\relatedProperties\PropertyType::CODE_LIST]))
        {
            $options = $this->getRelatedPropertyOptions($property);
            if (count($options) > 1)
            {
                return true;
            } else
            {
                return false;
            }
        }

        return true;
    }

    protected $_relatedOptions = [];

    /**
     *
     * Получение доступных опций для свойства
     * @param CmsContentProperty $property
     * @return $this|array|\yii\db\ActiveRecord[]
     */
    public function getRelatedPropertyOptions($property)
    {
        $options = [];

        if (isset($this->_relatedOptions[$property->code]))
        {
            return $this->_relatedOptions[$property->code];
        }

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

        $this->_relatedOptions[$property->code] = $options;

        return $options;
    }






    /**
     * @param $property
     * @return bool
     */
    public function isShowOfferProperty($property)
    {
        if (!in_array($property->code, $this->offerRelatedProperties))
        {
            return false;
        }

        if ($this->onlyExistsFilters === false)
        {
            return true;
        }

        if (in_array($property->property_type, [\skeeks\cms\relatedProperties\PropertyType::CODE_ELEMENT, \skeeks\cms\relatedProperties\PropertyType::CODE_LIST]))
        {
            $options = $this->getOfferRelatedPropertyOptions($property);
            if (count($options) > 1)
            {
                return true;
            } else
            {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $property
     * @return bool
     */
    public function isShowPriceFilter()
    {
        if (!$this->typePrice)
        {
            return false;
        }

        if ($this->onlyExistsFilters === false)
        {
            return true;
        }

        if ($this->searchModel->price_from == $this->searchModel->price_to)
        {
            return false;
        }



        return true;
    }

    protected $_offerOptions = [];

    /**
     *
     * Получение доступных опций для свойства
     * @param CmsContentProperty $property
     * @return $this|array|\yii\db\ActiveRecord[]
     */
    public function getOfferRelatedPropertyOptions($property)
    {
        if (isset($this->_offerOptions[$property->code]))
        {
            return $this->_offerOptions[$property->code];
        }

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

        $this->_offerOptions[$property->code] = $options;

        return $options;
    }
}