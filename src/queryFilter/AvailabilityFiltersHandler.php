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
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\yii2\queryfilter\IQueryFilterHandler;
use v3project\yii2\productfilter\EavFiltersHandler;
use v3project\yii2\productfilter\IFiltersHandler;
use yii\base\Model;
use yii\data\DataProviderInterface;
use yii\db\QueryInterface;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * @property string $valueAsText
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AvailabilityFiltersHandler extends Model
    implements IQueryFilterHandler
{

    public $viewFile = '@skeeks/cms/shop/queryFilter/views/availability-filter-hidden';
    public $viewFileVisible = '@skeeks/cms/shop/queryFilter/views/availability-filter';

    public $value = 1;
    public $formName = 'a';

    public function formName()
    {
        return $this->formName;
    }

    public function init()
    {
        $this->value = (int) \Yii::$app->skeeks->site->shopSite->is_show_product_only_quantity;
        return parent::init();
    }
    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'value' => 'В наличии',
        ];
    }

    public function rules()
    {
        return [
            [['value'], 'integer'],
        ];
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
     * @param QueryInterface $activeQuery
     * @return $this
     */
    public function applyToQuery(QueryInterface $activeQuery)
    {
        if ($this->value == 1) {
            $activeQuery->joinWith('shopProduct as shopProduct');
            //$activeQuery->joinWith('shopProduct.shopProductOffers as shopProductOffers');

            $activeQuery->joinWith('shopProduct.shopStoreProducts as shopStoreProducts');
            $activeQuery->joinWith('shopProduct.shopProductOffers.shopStoreProducts as shopOffersStoreProducts');

            $activeQuery->andWhere([
                'or',
                ['>', 'shopStoreProducts.quantity', 0],
                ['>', 'shopOffersStoreProducts.quantity', 0],
            ]);
            $activeQuery->groupBy([ShopCmsContentElement::tableName() . ".id"]);
        } elseif ($this->value == 2) {
            $activeQuery->joinWith('shopProduct as shopProduct');
            $activeQuery->joinWith('shopProduct.shopProductOffers as shopProductOffers');

            $activeQuery->joinWith('shopProduct.shopStoreProducts as shopStoreProducts');
            $activeQuery->joinWith('shopProduct.shopProductOffers.shopStoreProducts as shopOffersStoreProducts');

            $activeQuery->andWhere([
                'or',
                ['>', 'shopProduct.quantity', 0],
                ['>', 'shopProductOffers.quantity', 0],
                ['>', 'shopStoreProducts.quantity', 0],
                ['>', 'shopOffersStoreProducts.quantity', 0],
            ]);
            $activeQuery->groupBy([ShopCmsContentElement::tableName() . ".id"]);
        }

        return $this;
    }


    public function getSelected()
    {

        if ($this->value == 1) {
            return [
                'availability' => "В наличии",
            ];
        }

        return [];
    }

    public function getValueAsText()
    {
        return (string) ArrayHelper::getValue($this->getOptions(), $this->value);
    }

    public function getOptions()
    {
        return [
            0 => 'Все',
            1 => 'В наличии',
            2 => 'В наличии и под заказ',
        ];
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
