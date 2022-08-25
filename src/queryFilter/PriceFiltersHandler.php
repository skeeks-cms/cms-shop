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
use v3p\aff\models\V3pShopCmsContentElement;
use v3project\yii2\productfilter\EavFiltersHandler;
use v3project\yii2\productfilter\IFiltersHandler;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\DataProviderInterface;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;
use yii\db\QueryInterface;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * @property float       $minValue
 * @property float       $maxValue
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
    public $cms_content_element_ids = [];
    public $f;
    public $t;
    public $formName = 'p';
    /**
     * @var ActiveQuery
     */
    protected $_baseQuery;
    protected $_min_max_data = null;
    public function formName()
    {
        return $this->formName.$this->type_price_id;
    }
    public function init()
    {
        parent::init();

        if (!$this->type_price_id) {
            $typePrice = \Yii::$app->shop->baseTypePrice;
            if ($typePrice) {
                $this->type_price_id = $typePrice->id;
            }
        }

        if (!$this->type_price_id) {
            throw new InvalidConfigException('Need parametr price_type_id');
        }
    }
    /**
     * @return ActiveQuery
     */
    public function getBaseQuery()
    {
        return $this->_baseQuery;
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
    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'f' => 'Цена от',
            't' => 'Цена до',
        ];
    }
    public function rules()
    {
        return [
            [['f'], 'number'],
            [['t'], 'number'],
        ];
    }
    public function getMinValue()
    {
        $data = $this->_getMinMaxPriceFromQuery();
        if (isset($data[0])) {
            return $data[0];
        }

        return 0;
    }
    /**
     * @return array
     */
    protected function _getMinMaxPriceFromQuery()
    {

        if ($this->_min_max_data !== null) {
            return $this->_min_max_data;
        }

        $query = clone $this->baseQuery;
        $query->joinWith('shopProduct as shopProduct');
        $query->joinWith('shopProduct.shopProductPrices as prices');
        $query->andWhere(['prices.type_price_id' => $this->type_price_id]);

        if (!\Yii::$app->skeeks->site->shopSite->is_show_product_no_price) {
            $query->andWhere(['>', 'prices.price', 0]);
        }

        $query->orderBy = [];
        $query->groupBy = [];
        $query->with = [];

        /*$query = ShopCmsContentElement::find();
        $query->joinWith('shopProduct');
        $query->joinWith('shopProduct.shopProductPrices as prices');
        $query->andWhere(['prices.type_price_id' => $this->type_price_id]);
        $query->andWhere('cms_content_element.id IN ('.$this->cms_content_element_ids.")");*/

        $query->joinWith('shopProduct.shopProductPrices.currency as currency');

        $query->select([
            'min' => "IF(shopProduct.product_type != 'offers', 
                           (currency.course * prices.price), 
                          (
                                SELECT MIN(p.price) FROM shop_product_price as p 
                                LEFT JOIN `shop_product` spo ON spo.`id` = p.product_id
                                
                                WHERE  
                                p.`type_price_id` = {$this->type_price_id}
                                AND spo.offers_pid = cms_content_element.id 
                            )
                          )",
            'max' => "IF(shopProduct.product_type != 'offers', 
                           (currency.course * prices.price), 
                          (
                                SELECT MIN(p.price) FROM shop_product_price as p 
                                LEFT JOIN `shop_product` spo ON spo.`id` = p.product_id
                                WHERE  
                                p.`type_price_id` = {$this->type_price_id}
                                AND spo.offers_pid = cms_content_element.id 
                            )
                          )",
        ]);


        /*$query->select([
            'min(currency.course * prices.price) as min',
            'max(currency.course * prices.price) as max',
        ]);*/

        $outerQuery = (new Query())->from(['inner' => $query])->select([
            'min' => 'min(min)',
            'max' => 'max(max)',
        ]);
        //echo $outerQuery->createCommand()->rawSql;die;
        //echo $query->createCommand()->rawSql;die;
        $data = $outerQuery->createCommand()->queryOne();

        $this->_min_max_data = [
            round(ArrayHelper::getValue($data, 'min'), 2),
            round(ArrayHelper::getValue($data, 'max'), 2),
        ];

        return $this->_min_max_data;
    }
    public function getMaxValue()
    {
        $data = $this->_getMinMaxPriceFromQuery();
        if (isset($data[1])) {
            return $data[1];
        }

        return 0;
    }
    /**
     * @param DataProviderInterface $dataProvider
     * @return $this
     */
    public function applyToDataProvider(DataProviderInterface $dataProvider)
    {
        return $this->applyToQuery($dataProvider->query);
    }

    static public function addSelectRealPrice($query, $type_price_id = null)
    {
        if (!$type_price_id) {
            $type_price_id = \Yii::$app->shop->baseTypePrice->id;
        }

        if (!isset($query->select['realPrice'])) {
            
            $quantitySql = '';
            if (\Yii::$app->skeeks->site->shopSite->is_show_product_only_quantity) {
                $quantitySql = "AND (`shopStoreProductsInner`.`quantity` > 0)";
            }
            
            /**
             * @var $query ActiveQuery
             */
            $query->innerJoinWith('shopProduct as shopProduct');
            $query->innerJoin(['prices' => 'shop_product_price'], [
                'prices.product_id'    => new Expression('shopProduct.id'),
                'prices.type_price_id' => $type_price_id,
            ]);
            $query->innerJoin(['currency' => 'money_currency'], ['currency.code' => new Expression('prices.currency_code')]);

            $query->addSelect([
                'realPrice' => "IF(shopProduct.product_type != 'offers', 
               (
                    currency.course * prices.price
                ), 
              (
                    SELECT MIN(p.price) FROM shop_product_price as p 
                    INNER JOIN `shop_product` spo ON spo.`id` = p.product_id
                    INNER JOIN `shop_store_product` `shopStoreProductsInner` ON
                    (
                        `shopStoreProductsInner`.`shop_product_id` = p.product_id
                    )
                    WHERE  
                    p.`type_price_id` = {$type_price_id} 
                    AND spo.offers_pid = cms_content_element.id 
                    {$quantitySql}
                )
              )",
            ]);
        }

        return $query;
    }
    /**
     * @param QueryInterface $activeQuery
     * @return $this
     */
    public function applyToQuery(QueryInterface $activeQuery)
    {
        $query = $activeQuery;

        if ($this->type_price_id) {

            

            if ($this->t || $this->f) {

                self::addSelectRealPrice($activeQuery, $this->type_price_id);

            }

            if ($this->t) {
                $query->andHaving(['<=', 'realPrice', (float)$this->t]);
            }
            if ($this->f) {
                $query->andHaving(['>=', 'realPrice', (float)$this->f]);
            }
        }

        return $this;
    }

    public function render(ActiveForm $form)
    {
        return \Yii::$app->view->render($this->viewFile, [
            'form'    => $form,
            'handler' => $this,
        ]);
    }

    public function renderVisible(ActiveForm $form = null)
    {
        return \Yii::$app->view->render($this->viewFileVisible, [
            'form'    => $form,
            'handler' => $this,
        ]);
    }
}
