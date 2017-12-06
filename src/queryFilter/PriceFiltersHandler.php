<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link https://skeeks.com/
 * @copyright (c) 2010 SkeekS
 * @date 13.11.2017
 */

namespace skeeks\cms\shop\queryFilter;

use backend\models\cont\Feature;
use backend\models\cont\FeatureValue;
use common\models\V3pFeature;
use common\models\V3pFeatureValue;
use common\models\V3pFtSoption;
use common\models\V3pProduct;
use skeeks\yii2\queryfilter\IQueryFilterHandler;
use v3project\yii2\productfilter\EavFiltersHandler;
use v3project\yii2\productfilter\IFiltersHandler;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\DataProviderInterface;
use yii\db\ActiveQuery;
use yii\db\QueryInterface;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * @property float $minValue
 * @property float $maxValue
 * @property ActiveQuery $baseQuery
 *
 * Class AvailabilityFiltersHandler
 * @package skeeks\cms\shop\queryFilter
 */
class PriceFiltersHandler extends Model
    implements IQueryFilterHandler
{

    public $viewFile = '@skeeks/cms/shop/queryFilter/views/price-filter-hidden';
    public $viewFileVisible = '@skeeks/cms/shop/queryFilter/views/price-filter';

    /**
     * @var int
     */
    public $type_price_id;
    /**
     * @var ActiveQuery
     */
    protected $_baseQuery;

    public $from;
    public $to;

    public $formName = 'price';

    public function formName()
    {
        return $this->formName . $this->type_price_id;
    }

    public function init()
    {
        parent::init();

        if (!$this->type_price_id) {
            $typePrice = \skeeks\cms\shop\models\ShopTypePrice::find()->andWhere(['def' => 'Y'])->one();
            if ($typePrice) {
                $this->type_price_id = $typePrice->id;
            }
        }

        if (!$this->type_price_id) {
            throw new InvalidConfigException('Need parametr price_type_id');
        }
    }

    /**
     * @param QueryInterface $baseQuery
     * @return $this
     */
    public function setBaseQuery(QueryInterface $baseQuery) {
        $this->_baseQuery = clone $baseQuery;
        return $this;
    }

    /**
     * @return ActiveQuery
     */
    public function getBaseQuery() {
        return $this->_baseQuery;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'from' => 'Цена от',
            'to' => 'Цена до',
        ];
    }

    public function rules()
    {
        return [
            [['from'], 'number'],
            [['to'], 'number']
        ];
    }

    public function getMinValue()
    {
        $query = clone $this->baseQuery;
        $query->joinWith('shopProduct as p');
        $query->joinWith('shopProduct.shopProductPrices as prices');
        $query->andWhere(['prices.type_price_id' => $this->type_price_id]);
        $query->orderBy = [];
        $query->groupBy = [];
        $query->with = [];
        $min = $query->select(['min(price) as value'])->createCommand()->queryOne();
        if ($min) {
            return ArrayHelper::getValue($min, 'value');
        }

        return 0;
    }


    public function getMaxValue()
    {
        $query = clone $this->baseQuery;
        $query->joinWith('shopProduct as p');
        $query->joinWith('shopProduct.shopProductPrices as prices');
        $query->andWhere(['prices.type_price_id' => $this->type_price_id]);
        $query->orderBy = [];
        $query->groupBy = [];
        $query->with = [];
        $min = $query->select(['max(price) as value'])->createCommand()->queryOne();
        if ($min) {
            return ArrayHelper::getValue($min, 'value');
        }

        return 0;
    }

    /**
     * @param QueryInterface $activeQuery
     * @return $this
     */
    public function applyToQuery(QueryInterface $activeQuery)
    {
        $query = $activeQuery;

        if ($this->type_price_id) {

            $query->joinWith('shopProduct as p');
            $query->joinWith('shopProduct.shopProductPrices as prices');
            $query->joinWith('shopProduct.shopProductPrices.currency as currency');
            $query->andWhere(['prices.type_price_id' => $this->type_price_id]);

            $query->select([
                'cms_content_element.*',
                'realPrice' => '( currency.course * prices.price )'
            ]);

            /*$query->leftJoin('shop_product', 'shop_product.id = cms_content_element.id');

            $query->leftJoin('shop_product_price', 'shop_product_price.product_id = shop_product.id');
            $query->leftJoin('money_currency', 'money_currency.code = shop_product_price.currency_code');*/

            //$query->andWhere(['shop_product_price.type_price_id' => $this->type_price_id]);


            /*$query->select([
                'cms_content_element.*',
                'realPrice' => '( (SELECT course FROM money_currency WHERE money_currency.code = shop_product_price.currency_code) * shop_product_price.price )'
            ]);*/




            if ($this->to) {
                $query->andHaving(['<=', 'realPrice', $this->to]);
            }
            if ($this->from) {
                $query->andHaving(['>=', 'realPrice', $this->from]);
            }
        }

        return $this;
    }

    /**
     * @param DataProviderInterface $dataProvider
     * @return $this
     */
    public function applyToDataProvider(DataProviderInterface $dataProvider)
    {
        return $this->applyToQuery($dataProvider->query);
    }

    public function getSelected()
    {

        if ($this->from && $this->to) {
            return ["from" => "от {$this->from} до {$this->to} руб."];
        }

        return [];
    }

    public function render(ActiveForm $form)
    {
        return \Yii::$app->view->render($this->viewFile, [
            'form' => $form,
            'handler' => $this
        ]);
    }

    public function renderVisible(ActiveForm $form = null)
    {
        return \Yii::$app->view->render($this->viewFileVisible, [
            'form' => $form,
            'handler' => $this
        ]);
    }
}
