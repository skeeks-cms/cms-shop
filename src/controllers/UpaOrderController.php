<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\shop\models\ShopBuyer;
use skeeks\cms\shop\models\ShopOrder;
use yii\base\Event;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\UnsetArrayValue;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class UpaOrderController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Orders');
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopOrder::class;

        $this->generateAccessActions = false;

        
        /*$this->permissionNames = [
            "shop/upa-order" => 'Доступ к персональной части',
        ];*/
        
        parent::init();
    }


    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [

            "index" => [
                "backendShowings" => false,
                "filters" => false,
                'grid'    => [
                    'on init' => function (Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;
                        $query->andWhere(['cms_user_id' => \Yii::$app->user->identity->id]);

                        /*$subQuery = ShopBuyer::find()->where(['cms_user_id' => \Yii::$app->user->identity->id])->select(['id']);
                        $query->andWhere(['in', 'shop_buyer_id', $subQuery]);*/
                    },


                    'defaultOrder' => [
                        //'is_created' => SORT_DESC,
                        'updated_at' => SORT_DESC,
                    ],

                    'visibleColumns' => [
                        //'updated_at',
                        'custom',
                        //'paid_at',
                        //'amount',
                    ],
                    'columns'        => [

                        'paid_at'              => [
                            'headerOptions' => [
                                'style' => 'width: 80px;'
                            ],
                            'contentOptions' => [
                                'style' => 'width: 80px;'
                            ],

                            'value' => function (ShopOrder $shopOrder, $key) {
                                $reuslt = "<div>";
                                if ($shopOrder->paid_at) {
                                    $this->view->registerJs(<<<JS
$('tr[data-key={$key}]').addClass('sx-tr-green');
JS
                                    );

                                    $this->view->registerCss(<<<CSS
tr.sx-tr-green, tr.sx-tr-green:nth-of-type(odd), tr.sx-tr-green td
{
background: #d5ffd5 !important;
}
CSS
                                    );
                                    $reuslt = "<div style='color: green;'>";
                                }

                                $reuslt .= $shopOrder->paid_at ? \Yii::$app->formatter->asDatetime($shopOrder->paid_at) : "-";
                                $reuslt .= "</div>";
                                return $reuslt;
                            },
                        ],
                        'updated_at'           => [
                            'headerOptions' => [
                                'style' => 'width: 120px;'
                            ],
                            'contentOptions' => [
                                'style' => 'width: 120px;'
                            ],
                            'value' => function(ShopOrder $shopOrder) {
                                return \Yii::$app->formatter->asRelativeTime($shopOrder->updated_at);
                            }
                        ],

                        'custom' => [
                            'attribute' => "id",
                            'format' => "raw",
                            'label' => "Заказ",
                            'value' => function (ShopOrder $shopOrder) {
                                $name = "Заказ №" . $shopOrder->id . ' на ' . $shopOrder->money;
                                if (!$shopOrder->shopOrderStatus) {
                                    return $name;
                                }

                                $data = [];

                                $data[] = Html::a($name, $shopOrder->url, [
                                         //'class' => "sx-trigger-action",
                                         'target' => "_blank",
                                         'data-pjax' => "0",
                                        'style' => "font-size: 18px;",
                                    ]) . " " .
                                    \yii\helpers\Html::tag("span", $shopOrder->shopOrderStatus->name, [
                                        'style' => "background: {$shopOrder->shopOrderStatus->bg_color}; color: {$shopOrder->shopOrderStatus->color}; padding: 5px; 0px;",
                                        //'class' => "label",
                                    ]);

                                $data[] = "от " . \yii\helpers\Html::tag("small", \Yii::$app->formatter->asDatetime($shopOrder->created_at)." (".\Yii::$app->formatter->asRelativeTime($shopOrder->created_at).")");

                                /*if ($shopOrder->shopPaySystem) {
                                    $data[] = "" . $shopOrder->shopPaySystem->name;
                                }

                                if ($shopOrder->shopDelivery) {
                                    $data[] = "" . $shopOrder->shopDelivery->name;
                                }*/
                                return implode("<br />", $data);
                            },
                        ],

                    ],

                ],
            ],

            'create' => new UnsetArrayValue(),
            'delete' => new UnsetArrayValue(),
            'delete-multi' => new UnsetArrayValue(),
            'update' => new UnsetArrayValue(),


            /*'payments' => [
                'class'    => BackendModelAction::class,
                'name'     => 'Платежи',
                'priority' => 400,
                'callback' => [$this, 'payments'],
                'icon'     => 'fas fa-credit-card',
            ],
            'bills'    => [
                'class'    => BackendModelAction::class,
                'name'     => 'Счета',
                'priority' => 400,
                'callback' => [$this, 'bills'],
                'icon'     => 'fas fa-credit-card',
            ],
            'changes'  => [
                'class'    => BackendModelAction::class,
                'name'     => 'Изменения по заказу',
                'priority' => 400,
                'callback' => [$this, 'changes'],
                'icon'     => 'fas fa-credit-card',
            ],*/

        ]);
    }
}