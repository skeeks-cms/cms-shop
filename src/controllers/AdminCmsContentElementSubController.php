<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.05.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\modules\admin\actions\modelEditor\AdminModelEditorAction;
use skeeks\cms\queryfilters\QueryFiltersEvent;
use skeeks\cms\shop\models\ShopProductPrice;
use skeeks\yii2\form\fields\SelectField;
use yii\base\Event;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminCmsContentElementSubController extends AdminCmsContentElementController
{

    public $editForm = '@skeeks/cms/shop/views/admin-cms-content-element/_form';

    public function initGridData($action, $content)
    {
        parent::initGridData($action, $content);

        $filterFields = [];
        $filterFields['is_ready'] = [
            'class'    => SelectField::class,
            'items'    => [
                'on' => 'Готов',
                'off' => 'Не привязан',
            ],
            'label'    => 'Привязка',
            'multiple' => false,
            'on apply' => function (QueryFiltersEvent $e) {
                /**
                 * @var $query ActiveQuery
                 */
                $query = $e->dataProvider->query;
                $query->joinWith('shopProduct as sp');

                if ($e->field->value) {
                    if ($e->field->value == 'on') {
                        $query->andWhere(['is not', 'sp.main_pid', null]);
                    } else {
                        $query->andWhere(['sp.main_pid' => null]);
                    }

                }
            },
        ];
        $filterFieldsRules[] = ['is_ready', 'safe'];

        $action->filters['filtersModel']['fields'] = ArrayHelper::merge((array)ArrayHelper::getValue($action->filters, ['filtersModel', 'fields']), $filterFields);
        $action->filters['filtersModel']['rules'] = ArrayHelper::merge((array)ArrayHelper::getValue($action->filters, ['filtersModel', 'rules']), $filterFieldsRules);
        $action->filters['visibleFilters'] = ArrayHelper::merge((array)ArrayHelper::getValue($action->filters, ['visibleFilters']), [
            'shop_supplier_id',
            'is_ready'
        ]);

        //Приджоивание магазинных данных
        $action->grid['on init'] = function (Event $event) {
            /**
             * @var $query ActiveQuery
             */
            $query = $event->sender->dataProvider->query;
            if ($this->content) {
                $query->andWhere(['content_id' => $this->content->id]);
            }

            $query->joinWith('shopProduct as sp');
            $query->joinWith('shopProduct.shopSupplier as shopSupplier');

            $query->andWhere([
                //'or',
                //['is not', 'shopSupplier.is_main', null],
                'shopSupplier.is_main' => 0
            ]);

            if (\Yii::$app->shop->shopTypePrices) {
                foreach (\Yii::$app->shop->shopTypePrices as $shopTypePrice) {
                    $query->leftJoin(["p{$shopTypePrice->id}" => ShopProductPrice::tableName()], [
                        "p{$shopTypePrice->id}.product_id"    => new Expression("sp.id"),
                        "p{$shopTypePrice->id}.type_price_id" => $shopTypePrice->id,
                    ]);
                }
            }
        };

    }

}
