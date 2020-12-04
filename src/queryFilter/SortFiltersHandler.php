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
use skeeks\cms\models\CmsContentElement;
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
class SortFiltersHandler extends Model
    implements IQueryFilterHandler
{

    public $viewFile = '@skeeks/cms/shop/queryFilter/views/sort-filter-hidden';
    public $viewFileVisible = '@skeeks/cms/shop/queryFilter/views/sort-filter';

    public $value = '-popular';
    public $formName = 's';

    public $type_price_id;

    public function formName()
    {
        return $this->formName;
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
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'value' => 'Сортировка',
        ];
    }

    public function rules()
    {
        return [
            [['value'], 'string'],
        ];
    }

    public function getSortOptions()
    {
        return [
            '-popular' => \Yii::t("skeeks/unify", "Сначала популярные"),
            'price'    => \Yii::t("skeeks/unify-shop", "Cheap first"),
            '-price'   => \Yii::t("skeeks/unify-shop", "Dear first"),
            '-new'     => \Yii::t("skeeks/unify", "Сначала новые"),
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
    public function applyToQuery(QueryInterface $query)
    {
        if ($this->value) {
            switch ($this->value) {
                case ('-popular'):
                    $query->orderBy([CmsContentElement::tableName().'.show_counter' => SORT_DESC]);
                    break;

                case ('-new'):
                    $query->orderBy([CmsContentElement::tableName().'.created_at' => SORT_DESC]);
                    break;

                case ('price'):
                    /*$query->joinWith('shopProduct.baseProductPrice as basePrice');
                    $query->orderBy(['baseProductPrice.price' => SORT_ASC]);*/

                    if ($this->type_price_id) {
                        //$query->joinWith('shopProduct as p');
                        //$query->joinWith('shopProduct.shopProductPrices as prices');
                        //$query->joinWith('shopProduct.shopProductPrices.currency as currency');
                        //$query->andWhere(['prices.type_price_id' => $this->type_price_id]);

                        $query->select([
                            'cms_content_element.*',
                            'realPrice' => '( currency.course * prices.price )',
                        ]);

                        $query->orderBy(['realPrice' => SORT_ASC]);

                    }

                    break;

                case ('-price'):

                    if ($this->type_price_id) {
                        /*$query->joinWith('shopProduct as p');
                        $query->joinWith('shopProduct.shopProductPrices as prices');
                        $query->joinWith('shopProduct.shopProductPrices.currency as currency');
                        $query->andWhere(['prices.type_price_id' => $this->type_price_id]);*/

                        $query->select([
                            'cms_content_element.*',
                            'realPrice' => '( currency.course * prices.price )',
                        ]);

                        $query->orderBy(['realPrice' => SORT_DESC]);
                    }

                    break;
            }
        }

        return $this;
    }

    public function getValueAsText()
    {
        return (string)ArrayHelper::getValue($this->getSortOptions(), $this->value);
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
