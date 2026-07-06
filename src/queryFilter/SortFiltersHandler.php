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
 * @property string $currentValue
 * 
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class SortFiltersHandler extends Model
    implements IQueryFilterHandler
{
    const SORT_POPULAR = '-popular';
    const SORT_PRICE_ASC = 'price';
    const SORT_PRICE_DESC = '-price';
    const SORT_NEW = '-new';
    const SORT_NAME_ASC = 'name';

    public $viewFile = '@skeeks/cms/shop/queryFilter/views/sort-filter-hidden';
    public $viewFileVisible = '@skeeks/cms/shop/queryFilter/views/sort-filter';

    public $value = self::SORT_POPULAR;
    public $formName = 's';

    public $type_price_id;

    /**
     * @return string[]
     */
    public static function getAvailableValues()
    {
        return [
            self::SORT_POPULAR,
            self::SORT_PRICE_ASC,
            self::SORT_PRICE_DESC,
            self::SORT_NEW,
            self::SORT_NAME_ASC,
        ];
    }

    /**
     * @return string[]
     */
    public static function getAvailableOptions()
    {
        return [
            self::SORT_POPULAR    => \Yii::t("skeeks/unify", "Сначала популярные"),
            self::SORT_PRICE_ASC  => \Yii::t("skeeks/unify-shop", "Cheap first"),
            self::SORT_PRICE_DESC => \Yii::t("skeeks/unify-shop", "Dear first"),
            self::SORT_NEW        => \Yii::t("skeeks/unify", "Сначала новые"),
            self::SORT_NAME_ASC   => \Yii::t("skeeks/unify", "По алфавиту"),
        ];
    }

    public function formName()
    {
        return $this->formName;
    }

    public function init()
    {
        $this->value = $this->currentValue;
        parent::init();


        if (!$this->type_price_id) {
            $typePrice = \Yii::$app->shop->baseTypePrice;
            if ($typePrice) {
                $this->type_price_id = $typePrice->id;
            }
        }
    }

    public function load($data, $formName = NULL)
    {
        $result = parent::load($data, $formName);
        if ($result) {
            \Yii::$app->session->set("sx-sort-value", $this->value);
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getCurrentValue()
    {
        if (\Yii::$app->session->getHasSessionId() || \Yii::$app->session->getIsActive()) {
            if (\Yii::$app->session->offsetExists("sx-sort-value")) {
                $value = (string) \Yii::$app->session->get("sx-sort-value");
                $options = $this->getSortOptions();
                if (isset($options[$value])) {
                    return $value;
                }
            }
        }

        return $this->defaultValue;
    }

    /**
     * @return string
     */
    public function getDefaultValue()
    {
        $value = (string)ArrayHelper::getValue(\Yii::$app->cms->cmsSite->shopSite, 'product_default_sort', self::SORT_POPULAR);
        $options = $this->getSortOptions();

        if (isset($options[$value])) {
            return $value;
        }

        return self::SORT_POPULAR;
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
        $options = static::getAvailableOptions();
        if (\Yii::$app->cms->cmsSite->shopSite->is_show_prices) {
            return $options;
        }

        unset($options[self::SORT_PRICE_ASC], $options[self::SORT_PRICE_DESC]);
        return $options;
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
                case (self::SORT_POPULAR):
                    $query->orderBy([
                        CmsContentElement::tableName(). '.priority' => SORT_ASC,
                        CmsContentElement::tableName().'.show_counter' => SORT_DESC
                    ]);
                    break;

                case (self::SORT_NEW):
                    $query->orderBy([CmsContentElement::tableName().'.published_at' => SORT_DESC]);
                    break;

                case (self::SORT_NAME_ASC):
                    $query->orderBy([CmsContentElement::tableName().'.name' => SORT_ASC]);
                    break;

                case (self::SORT_PRICE_ASC):
                    if ($this->type_price_id) {
                        PriceFiltersHandler::addSelectRealPrice($query, $this->type_price_id);
                        $query->orderBy(['realPrice' => SORT_ASC]);
                        
                    }

                    break;

                case (self::SORT_PRICE_DESC):

                    if ($this->type_price_id) {

                        PriceFiltersHandler::addSelectRealPrice($query, $this->type_price_id);
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
