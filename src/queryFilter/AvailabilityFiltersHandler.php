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
use yii\base\Model;
use yii\data\DataProviderInterface;
use yii\db\ActiveQuery;
use yii\db\QueryInterface;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * Class AvailabilityFiltersHandler
 * @package skeeks\cms\shop\queryFilter
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
            [['value'], 'integer']
        ];
    }


    /**
     * @param QueryInterface $activeQuery
     * @return $this
     */
    public function applyToQuery(QueryInterface $activeQuery)
    {
        if ($this->value == 1) {
            $activeQuery->joinWith('shopProduct as shopProduct');
            $activeQuery->andWhere(['>=', 'shopProduct.quantity', 1]);
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

        if ($this->value == 1) {
            return [
                'availability' => "В наличии"
            ];
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
