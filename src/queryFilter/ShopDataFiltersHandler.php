<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\queryFilter;

use backend\models\cont\Feature;
use backend\models\cont\FeatureValue;
use common\models\V3pFeature;
use common\models\V3pFeatureValue;
use common\models\V3pFtSoption;
use common\models\V3pProduct;
use skeeks\cms\models\CmsCountry;
use skeeks\cms\models\CmsSavedFilter;
use skeeks\cms\shop\models\ShopBrand;
use skeeks\yii2\queryfilter\IQueryFilterHandler;
use v3project\yii2\productfilter\EavFiltersHandler;
use v3project\yii2\productfilter\IFiltersHandler;
use yii\base\Model;
use yii\data\DataProviderInterface;
use yii\db\ActiveQuery;
use yii\db\QueryInterface;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * @property ActiveQuery $baseQuery
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopDataFiltersHandler extends Model
    implements IQueryFilterHandler
{

    public $brand_id = null;
    public $country = null;

    public $viewFile = '@app/views/filters/shop-data-filters';

    /**
     * @var ActiveQuery
     */
    protected $_baseQuery;

    public function formName()
    {
        return "sd";
    }

    public function rules()
    {
        return [
            [['brand_id'], 'safe'],
            [['country'], 'safe'],
        ];
    }

    /**
     * @return mixed|void
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (!$this->baseQuery) {
            throw new InvalidConfigException('Не указан базовый запрос');
        }
    }

    /**
     * @param QueryInterface $baseQuery
     * @return $this
     */
    public function setBaseQuery(QueryInterface $baseQuery)
    {
        $this->_baseQuery = clone $baseQuery;
        return $this;
    }

    public function getBrandOptions()
    {
        $query = clone $this->baseQuery;
        $query->joinWith('shopProduct as shopProduct');

        $query->orderBy = [];
        $query->groupBy = [];
        $query->with = [];

        $query->innerJoinWith('shopProduct.brand as brand');
        $query->addSelect(['brand_id' => 'brand.id', 'brand_name' => 'brand.name']);
        $query->groupBy(['brand.id']);
        $query->orderBy(['brand.name' => SORT_ASC]);

        $data = $query->asArray()->all();

        return ArrayHelper::map($data, "brand_id", "brand_name");

    }

    public function getCountryOptions()
    {
        $query = clone $this->baseQuery;
        $query->joinWith('shopProduct as shopProduct');

        $query->orderBy = [];
        $query->groupBy = [];
        $query->with = [];

        $query->innerJoinWith('shopProduct.country as country');
        $query->addSelect(['country_alpha2' => 'shopProduct.country_alpha2', 'country_name' => 'country.name']);
        $query->groupBy(['shopProduct.country_alpha2']);
        $query->orderBy(['country.name' => SORT_ASC]);

        $data = $query->asArray()->all();

        return ArrayHelper::map($data, "country_alpha2", "country_name");

    }

    /**
     * @return ActiveQuery
     */
    public function getBaseQuery()
    {
        return $this->_baseQuery;
    }


    public function loadFromSavedFilter(CmsSavedFilter $cmsSavedFilter)
    {
        if ($cmsSavedFilter->shop_brand_id) {
            $this->brand_id = [$cmsSavedFilter->shop_brand_id];
        }
        if ($cmsSavedFilter->country_alpha2) {
            $this->country = [$cmsSavedFilter->country_alpha2];
        }
    }

    public function applyToQuery(QueryInterface $activeQuery) {

        if ($this->country) {
            $activeQuery->andWhere(['shopProduct.country_alpha2' => $this->country]);
        }

        if ($this->brand_id) {
            $activeQuery->andWhere(['shopProduct.brand_id' => $this->brand_id]);
        }

        return $this;
    }


    protected $_applied = [];
    protected $_appliedResult = null;

    /**
     * @return array
     */
    public function getApplied()
    {
        $result = [];

        if ($this->_appliedResult !== null) {
            return (array) $this->_appliedResult;
        }

        if ($this->country) {
            foreach ($this->country as $val)
            {
                $country = CmsCountry::find()->alpha2($val)->one();
                $rowData = [
                    'name' => "произвдоства " . $country->name,
                    'type' => "",
                    'property_id' => "field-sd-country",
                    'value' => $val,
                ];
                $result[] = $rowData;
            }
        }


        if ($this->brand_id) {
            foreach ($this->brand_id as $val)
            {
                $brand = ShopBrand::findOne($val);
                $rowData = [
                    'name' => $brand->name,
                    'type' => "",
                    'property_id' => "field-sd-brand_id",
                    'value' => $val,
                ];
                $result[] = $rowData;
            }
        }

        $this->_appliedResult = $result;
        return $result;
    }


    /**
     * @param DataProviderInterface $dataProvider
     * @return $this
     */
    public function applyToDataProvider(DataProviderInterface $dataProvider)
    {
        return $this->applyToQuery($dataProvider->query);
    }

    /**
     * @param ActiveForm $form
     * @return string
     */
    public function render(ActiveForm $form)
    {
        return \Yii::$app->view->render($this->viewFile, [
            'form'    => $form,
            'handler' => $this,
        ]);
    }

    /**
     * @return null|CmsSavedFilter
     */
    public function getSavedFilter()
    {
        $data = $this->toArray();
        ArrayHelper::remove($data, "baseQuery");

        $cmsSavedFilter = null;

        $total = ArrayHelper::merge((array) $this->brand_id, (array) $this->country);
        if (count($total) == 1) {

            $qSavedFilter = CmsSavedFilter::find()->cmsSite()
                    ->andWhere([
                        'cms_tree_id' => \Yii::$app->cms->currentTree->id,
                    ]);

            if ($this->brand_id) {
                $qSavedFilter->andWhere(['shop_brand_id' => (int) ArrayHelper::getValue($this->brand_id, "0")]);
            } elseif ($this->country) {
                $qSavedFilter->andWhere(['country_alpha2' => (string) ArrayHelper::getValue($this->country, "0")]);
            }

             $cmsSavedFilter = $qSavedFilter->one();
            if (!$cmsSavedFilter) {
                $cmsSavedFilter = new CmsSavedFilter();
                $cmsSavedFilter->cms_tree_id = \Yii::$app->cms->currentTree->id;

                if ($this->brand_id) {
                    $cmsSavedFilter->shop_brand_id = (int) ArrayHelper::getValue($this->brand_id, "0");
                } elseif ($this->country) {
                    $cmsSavedFilter->country_alpha2 = (int) ArrayHelper::getValue($this->country, "0");
                }

                if (!$cmsSavedFilter->save()) {
                    $cmsSavedFilter = null;
                }
            }
        }


        return $cmsSavedFilter;
    }

}
