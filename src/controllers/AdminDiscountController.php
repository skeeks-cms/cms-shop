<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\actions\backend\BackendModelMultiActivateAction;
use skeeks\cms\actions\backend\BackendModelMultiDeactivateAction;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\grid\DefaultActionColumn;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopDiscount;
use yii\base\Event;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminDiscountController extends BackendModelStandartController
{
    public $notSubmitParam = 'sx-not-submit';

    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Discount goods');
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopDiscount::class;

        parent::init();
    }


    /**
     * @inheritdoc
     */
    public function actions()
    {


        return ArrayHelper::merge(parent::actions(), [

            'index' => [
                'backendShowings' => false,

                "filters" => [
                    'visibleFilters' => [
                        //'id',
                        'name',
                    ],

                    'filtersModel' => [
                        'fields' => [
                            'name' => [
                                'isAllowChangeMode' => false,
                            ],
                        ],
                    ],
                ],

                "grid" => [
                     'on init' => function (Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;

                        $query->andWhere(['cms_site_id' => \Yii::$app->skeeks->site->id]);
                    },
                    //'on init'       => function (Event $e) {
                    /**
                     * @var $dataProvider ActiveDataProvider
                     * @var $query ActiveQuery
                     */
                    /*    $query = $e->sender->dataProvider->query;
                        $dataProvider = $e->sender->dataProvider;

                        $query->joinWith('cmsSiteDomains');
                        $query->groupBy(CmsSite::tableName() . ".id");
                        $query->select([
                            CmsSite::tableName() . '.*',
                            'countDomains' => new Expression("count(*)")
                        ]);
                    },*/

                    /*'sortAttributes' => [
                        'countDomains' => [
                            'asc' => ['countDomains' => SORT_ASC],
                            'desc' => ['countDomains' => SORT_DESC],
                            'label' => 'Количество доменов',
                            'default' => SORT_ASC
                        ]
                    ],*/
                    'defaultOrder'   => [
                        'priority' => SORT_ASC,
                    ],
                    'visibleColumns' => [
                        'checkbox',
                        'actions',

                        'name',

                        'value',

                        'is_active',
                        'is_last',

                        'priority',
                    ],
                    'columns'        => [
                        'name'        => [
                            'class' => DefaultActionColumn::class,
                        ],
                        'is_active'        => [
                            'class' => BooleanColumn::class,
                        ],
                        'is_last' => [
                            'class' => BooleanColumn::class,
                        ],

                        'value' => [
                            'value' => function (\skeeks\cms\shop\models\ShopDiscount $shopDiscount) {
                                if ($shopDiscount->value_type == \skeeks\cms\shop\models\ShopDiscount::VALUE_TYPE_P) {
                                    return \Yii::$app->formatter->asPercent($shopDiscount->value / 100);
                                } else {
                                    $money = new \skeeks\cms\money\Money((string)$shopDiscount->value, $shopDiscount->currency_code);
                                    return (string)$money;
                                }
                            },
                        ],
                    ],
                ],
            ],

            /*"create" => [
                'fields' => [$this, 'updateFields'],
            ],

            "update" => [
                'fields' => [$this, 'updateFields'],
            ],*/

            "activate-multi" => [
                'class' => BackendModelMultiActivateAction::class,
            ],

            "deactivate-multi" => [
                'class' => BackendModelMultiDeactivateAction::class,
            ],
        ]);
    }

}
