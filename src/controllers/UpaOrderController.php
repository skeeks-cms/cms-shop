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
                "filters" => [
                    "visibleFilters" => [
                        'id',
                    ],
                ],
                'grid'    => [
                    'on init' => function (Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;

                        $subQuery = ShopBuyer::find()->where(['cms_user_id' => \Yii::$app->user->identity->id])->select(['id']);
                        $query->andWhere(['in', 'shop_buyer_id', $subQuery]);
                    },


                    'defaultOrder' => [
                        //'is_created' => SORT_DESC,
                        'updated_at' => SORT_DESC,
                    ],

                    'visibleColumns' => [
                        //'checkbox',
                        //'actions',
                        'id',

                        'updated_at',

                        'shop_order_status_id',

                        'paid_at',
                        'canceled_at',

                        //'shop_buyer_id',
                        //'shop_pay_system_id',
                        //'shop_delivery_id',

                        //'items',

                        'amount',
                        //'is_created',
                        'go',
                    ],
                    'columns'        => [

                        /*'id' => [
                            'value' => function (ShopOrder $shopOrder) {
                                $result = [];

                                $result[] = $shopOrder->asText;
                                return implode("<br />", $result);
                            },
                        ],*/

                        'is_created'           => [
                            'class' => BooleanColumn::class,
                        ],
                        'paid_at'              => [
                            'class' => DateTimeColumnData::class,
                        ],
                        'go'                   => [
                            'format' => "raw",
                            'value'  => function (ShopOrder $shopOrder) {
                                return \yii\helpers\Html::a('<i class="fas fa-external-link-alt"></i>', $shopOrder->url, [
                                    'target'    => '_blank',
                                    'title'     => \Yii::t('skeeks/cms', 'Watch to site (opens new window)'),
                                    'data-pjax' => '0',
                                    'class'     => 'btn btn-default btn-sm',
                                ]);
                            },
                        ],
                        'canceled_at'          => [
                            'value' => function (ShopOrder $shopOrder, $key) {
                                $reuslt = "<div>";
                                if ($shopOrder->canceled_at) {
                                    $this->view->registerJs(<<<JS
$('tr[data-key={$key}]').addClass('sx-tr-red');
JS
                                    );

                                    $this->view->registerCss(<<<CSS
tr.sx-tr-red, tr.sx-tr-red:nth-of-type(odd), tr.sx-tr-red td
{
background: #FFECEC !important;
}
CSS
                                    );
                                    $reuslt = "<div style='color: red;'>";
                                }

                                $reuslt .= $shopOrder->canceled_at ? \Yii::$app->formatter->asDatetime($shopOrder->canceled_at) : "-";
                                $reuslt .= "</div>";
                                return $reuslt;
                            },
                        ],
                        'paid_at'              => [
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
                            'class' => DateTimeColumnData::class,
                        ],

                        'shop_order_status_id' => [
                            'value' => function (ShopOrder $shopOrder) {
                                if (!$shopOrder->shopOrderStatus) {
                                    return "-";
                                }
                                return \yii\helpers\Html::label($shopOrder->shopOrderStatus->asText, null, [
                                        'style' => "background: {$shopOrder->shopOrderStatus->color}",
                                        'class' => "label u-label",
                                    ])."<br />".
                                    \yii\helpers\Html::tag("small",
                                        \Yii::$app->formatter->asDatetime($shopOrder->status_at)." (".\Yii::$app->formatter->asRelativeTime($shopOrder->status_at).")");
                            },
                        ],
                        'amount'               => [
                            'value' => function (ShopOrder $shopOrder) {
                                $result = [];
                                $result[] = "Товары:&nbsp;".$shopOrder->moneyItems;
                                $result[] = "Доставка:&nbsp;".$shopOrder->moneyDelivery;
                                $result[] = "Скидка:&nbsp;".$shopOrder->moneyDiscount;
                                $result[] = "Налог:&nbsp;".$shopOrder->moneyVat;
                                return "К&nbsp;оплате:&nbsp;<b>".$shopOrder->money."</b><hr style='margin: 0px; padding: 0px;'/>".implode("<br />", $result);
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