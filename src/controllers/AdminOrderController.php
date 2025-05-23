<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\actions\BackendGridModelRelatedAction;
use skeeks\cms\backend\actions\BackendModelAction;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\helpers\Image;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsUser;
use skeeks\cms\shop\models\ShopBasket;
use skeeks\cms\shop\models\ShopFuser;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\shop\models\ShopProduct;
use yii\base\Event;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Class AdminExtraController
 * @package skeeks\cms\shop\controllers
 */
class AdminOrderController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Orders');
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopOrder::class;

        $this->generateAccessActions = false;
        $this->permissionName = "shop/admin-order";

        /*$this->modelHeader = function () {
            /**
             * @var $model ShopOrder
            $model = $this->model;
            $date = \Yii::$app->formatter->asDatetime($model->created_at);
            return Html::tag('h1', "Заказ <span class='g-color-primary'>№{$model->id}</span> на сумму<span class='g-color-primary'> " . $model->money . "</span>" .  Html::a('<i class="fas fa-external-link-alt"></i>', $model->getPublicUrl(), [
                    'target' => "_blank",
                    'class'  => "g-ml-20",
                    'title'  => \Yii::t('skeeks/cms', 'Watch to site (opens new window)'),
                ]), [
                    'style'  => "margin-bottom: 0px;",
                ])
                .
                "<h6 style='color: gray;'>от " . Html::tag("span", \Yii::$app->formatter->asDatetime($model->created_at)) . "</h6>";
                ;
        };*/

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [

            "index" => [
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

                        $query->cmsSite()->isCreated();
                        /*$paymentsQuery = CrmPayment::find()->select(['count(*)'])->where([
                            'or',
                            ['sender_crm_contractor_id' => new Expression(CrmContractor::tableName().".id")],
                            ['receiver_crm_contractor_id' => new Expression(CrmContractor::tableName().".id")],
                        ]);

                        $contactsQuery = CrmContractorMap::find()->select(['count(*)'])->where([
                            'crm_company_id' => new Expression(CrmContractor::tableName().".id"),
                        ]);

                        $senderQuery = CrmPayment::find()->select(['sum(amount) as amount'])->where([
                            'sender_crm_contractor_id' => new Expression(CrmContractor::tableName().".id"),
                        ]);

                        $receiverQuery = CrmPayment::find()->select(['sum(amount) as amount'])->where([
                            'receiver_crm_contractor_id' => new Expression(CrmContractor::tableName().".id"),
                        ]);

                        $query->select([
                            CrmContractor::tableName().'.*',
                            'count_payemnts'      => $paymentsQuery,
                            'count_contacts'      => $contactsQuery,
                            'sum_send_amount'     => $senderQuery,
                            'sum_receiver_amount' => $receiverQuery,
                        ]);*/
                    },


                    'defaultOrder' => [
                        //'is_created' => SORT_DESC,
                        'created_at' => SORT_DESC,
                    ],

                    'visibleColumns' => [
                        /*'checkbox',*/
                        'actions',
                        //'id',

                        'created_at',

                        'custom',

                        'amount',
                        'paid_at',

                        'shop',

                        //'shop_buyer_id',
                        //'buyer',
                        //'shop_pay_system_id',
                        //'shop_delivery_id',

                        //'items',


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

                        'is_created' => [
                            'class' => BooleanColumn::class,
                        ],
                        'paid_at'    => [
                            'class' => DateTimeColumnData::class,
                        ],
                        'go'         => [
                            'format'         => "raw",
                            'value'          => function (ShopOrder $shopOrder) {
                                return \yii\helpers\Html::a('<i class="fas fa-external-link-alt"></i>', $shopOrder->url, [
                                    'target'    => '_blank',
                                    'title'     => \Yii::t('skeeks/cms', 'Watch to site (opens new window)'),
                                    'data-pjax' => '0',
                                    'class'     => 'btn btn-default btn-sm',
                                ]);
                            },
                            'headerOptions'  => [
                                'style' => 'max-width: 40px; width: 40px;',
                            ],
                            'contentOptions' => [
                                'style' => 'max-width: 40px; width: 40px;',
                            ],
                        ],
                        'shop'       => [
                            'format' => "raw",
                            'label'  => "Магазин",
                            'value'  => function (ShopOrder $shopOrder) {
                                $data = [];
                                if ($shopOrder->shop_store_id) {
                                    $data[] = $shopOrder->shopStore->name;
                                }
                                if ($shopOrder->shop_cashebox_id) {
                                    $data[] = $shopOrder->shopCashebox->name;
                                }
                                if ($shopOrder->shop_cashebox_shift_id) {
                                    $data[] = $shopOrder->shopCasheboxShift->asText;
                                }

                                return implode("<br />", $data);
                            },

                        ],

                        'buyer' => [
                            'format' => "raw",
                            'label'  => "Покупатель",
                            'value'  => function (ShopOrder $shopOrder) {
                                $data = [];
                                if ($shopOrder->shopBuyer) {
                                    $data[] = $shopOrder->shopBuyer->asText;

                                    if ($shopOrder->shopBuyer->cmsUser) {
                                        $data[] = '<i class="fas fa-user"></i>&nbsp;'.$shopOrder->shopBuyer->cmsUser->asText;
                                    } else {
                                        $data[] = '<small style="color: gray;"><i class="fas fa-user"></i>&nbsp;Неавторизован</small>';
                                    }

                                } else {
                                    if ($shopOrder->shopCart && $shopOrder->shopCart->cmsUser) {
                                        $data[] = '<i class="fas fa-user"></i>&nbsp;'.$shopOrder->shopCart->cmsUser->asText;
                                    } else {
                                        $data[] = '<small style="color: gray;"><i class="fas fa-user"></i>&nbsp;'."Неавторизован</small>";
                                    }
                                }


                                return implode("<br />", $data);
                            },

                        ],

                        'paid_at'              => [
                            'headerOptions'  => [
                                'style' => 'width: 80px;',
                            ],
                            'contentOptions' => [
                                'style' => 'width: 80px; text-align: center;',
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

                                $reuslt .= $shopOrder->paid_at ? "<span style='color: green;' title='".\Yii::$app->formatter->asDatetime($shopOrder->paid_at)."'>✓</span>" : "";
                                $reuslt .= "</div>";
                                return $reuslt;
                            },
                        ],
                        'created_at'           => [
                            'headerOptions'  => [
                                'style' => 'width: 120px;',
                            ],
                            'contentOptions' => [
                                'style' => 'width: 120px;',
                            ],
                            'class'          => DateTimeColumnData::class,
                            'view_type'      => DateTimeColumnData::VIEW_DATE,
                        ],
                        'items'                => [
                            'label'  => "Товары",
                            'format' => "raw",
                            'value'  => function (ShopOrder $shopOrder) {
                                if ($shopOrder->shopOrderItems) {

                                    $result = [];

                                    foreach ($shopOrder->shopOrderItems as $shopBasket) {
                                        $result[] = "<div style='min-width: 300px;'>".

                                            \yii\helpers\Html::img(Image::getSrc($shopBasket->image ? $shopBasket->image->src : null), [
                                                'style' => "max-height: 30px; max-width: 30px; border-radius: 5px;",
                                            ])
                                            .
                                            \yii\helpers\Html::a($shopBasket->name, $shopBasket->url, [
                                                'target'    => '_blank',
                                                'data-pjax' => '0',
                                            ])
                                            .
                                            <<<HTML
                 — $shopBasket->quantity $shopBasket->measure_name</div>
HTML;

                                    }

                                    return implode('<hr style="margin: 0px;"/>', $result);
                                }
                            },
                        ],
                        'custom'               => [
                            'attribute' => "id",
                            'format'    => "raw",
                            'label'     => "Номер заказа",
                            'value'     => function (ShopOrder $shopOrder) {
                                $name = $shopOrder->asText;
                                if (!$shopOrder->shopOrderStatus) {
                                    return $name;
                                }

                                $data = [];

                                $data[] = Html::a($name, "#", [
                                    'class' => "sx-trigger-action",
                                    //'style' => "font-size: 18px;",
                                ]);

                                if ($shopOrder->is_order) {
                                    $data[] = \yii\helpers\Html::tag("span", $shopOrder->shopOrderStatus->name, [
                                        'style' => "background: {$shopOrder->shopOrderStatus->bg_color}; color: {$shopOrder->shopOrderStatus->color}; padding: 5px; 0px;",
                                        //'class' => "label",
                                    ]);
                                }


                                //$data[] = "от " . \yii\helpers\Html::tag("small", \Yii::$app->formatter->asDatetime($shopOrder->created_at)." (".\Yii::$app->formatter->asRelativeTime($shopOrder->created_at).")");

                                /*if ($shopOrder->shopPaySystem) {
                                    $data[] = "" . $shopOrder->shopPaySystem->name;
                                }
                                
                                if ($shopOrder->shopDelivery) {
                                    $data[] = "" . $shopOrder->shopDelivery->name;
                                }*/
                                return implode(" ", $data);
                            },
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
                            'contentOptions' => [
                                'style' => 'width: 120px;',
                            ],

                            'value' => function (ShopOrder $shopOrder) {
                                return Html::tag('span', $shopOrder->money, [
                                    'class' => 'g-color-primary',
                                ]);
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

            'create' => [
                'isVisible' => false,
            ],

            /*'create-order' => [

                'class'    => AdminAction::class,
                'name'     => \Yii::t('skeeks/shop/app', 'Place your order'),
                "icon"     => "fa fa-plus",
                "callback" => [$this, 'createOrder'],
            ],*/

            'payments' => [
                'class'    => BackendGridModelRelatedAction::class,
                'name'     => 'Платежи',
                'priority' => 400,
                //'callback' => [$this, 'payments'],
                'icon'     => 'fas fa-credit-card',


                'controllerRoute' => "/shop/admin-payment",
                'relation'        => ['shop_order_id' => 'id'],
                'priority'        => 400,
                'on gridInit'     => function ($e) {
                    /**
                     * @var $action BackendGridModelRelatedAction
                     */
                    $action = $e->sender;
                    $action->relatedIndexAction->backendShowings = false;
                    $action->relatedIndexAction->filters = false;
                    $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];

                    ArrayHelper::removeValue($visibleColumns, 'shop_order_id');
                    $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                },


            ],
            'bills'    => [
                'class'    => BackendGridModelRelatedAction::class,
                'name'     => 'Счета',
                'priority' => 400,
                //'callback' => [$this, 'bills'],
                'icon'     => 'fas fa-credit-card',

                'controllerRoute' => "/shop/admin-bill",
                'relation'        => ['shop_order_id' => 'id'],
                'priority'        => 400,
                'on gridInit'     => function ($e) {
                    /**
                     * @var $action BackendGridModelRelatedAction
                     */
                    $action = $e->sender;
                    $action->relatedIndexAction->backendShowings = false;
                    $action->relatedIndexAction->filters = false;
                    $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];

                    ArrayHelper::removeValue($visibleColumns, 'shop_order_id');
                    $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                },

            ],

            'checks' => [
                'class'    => BackendGridModelRelatedAction::class,
                'name'     => 'Чеки',
                'priority' => 400,
                //'callback' => [$this, 'checks'],
                'icon'     => 'fas fa-credit-card',

                'controllerRoute' => "/shop/admin-shop-check",
                'relation'        => ['shop_order_id' => 'id'],
                'priority'        => 400,
                'on gridInit'     => function ($e) {
                    /**
                     * @var $action BackendGridModelRelatedAction
                     */
                    $action = $e->sender;
                    $action->relatedIndexAction->backendShowings = false;
                    $action->relatedIndexAction->filters = false;
                    $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];

                    ArrayHelper::removeValue($visibleColumns, 'shop_order_id');
                    $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                },
            ],

            'docmoves' => [
                'class'    => BackendGridModelRelatedAction::class,
                'name'     => 'Движение товара',
                'priority' => 400,
                //'callback' => [$this, 'docmoves'],
                'icon'     => 'fas fa-credit-card',


                'controllerRoute' => "/shop/admin-shop-store-doc-move",
                'relation'        => ['shop_order_id' => 'id'],
                'priority'        => 400,
                'on gridInit'     => function ($e) {
                    /**
                     * @var $action BackendGridModelRelatedAction
                     */
                    $action = $e->sender;
                    $action->relatedIndexAction->backendShowings = false;
                    $action->relatedIndexAction->filters = false;
                    $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];

                    ArrayHelper::removeValue($visibleColumns, 'shop_order_id');
                    $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                },
            ],



            "bonus" => [
                'class'           => BackendGridModelRelatedAction::class,
                'accessCallback'  => true,
                'name'            => "Бонусы",
                'icon'            => 'fa fa-list',
                'controllerRoute' => "/shop/admin-bonus-transaction",
                'relation'        => ['shop_order_id' => 'id'],
                'priority'        => 400,
                'on gridInit'     => function ($e) {
                    /**
                     * @var $action BackendGridModelRelatedAction
                     */
                    $action = $e->sender;
                    $action->relatedIndexAction->backendShowings = false;
                    $action->relatedIndexAction->filters = false;
                    $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];

                    ArrayHelper::removeValue($visibleColumns, 'shop_order_id');
                    $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                },
            ],


            'changes' => [
                'class'    => BackendGridModelRelatedAction::class,
                'name'     => 'Лог',
                'priority' => 400,
                //'callback' => [$this, 'changes'],
                //'icon'     => 'fa fa-list',

                'controllerRoute' => "/shop/admin-order-change",
                'relation'        => ['shop_order_id' => 'id'],
                'priority'        => 400,
                'on gridInit'     => function ($e) {
                    /**
                     * @var $action BackendGridModelRelatedAction
                     */
                    $action = $e->sender;
                    $action->relatedIndexAction->backendShowings = false;
                    $action->relatedIndexAction->filters = false;
                    $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];

                    ArrayHelper::removeValue($visibleColumns, 'shop_order_id');
                    $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                },
            ],

        ]);
    }



    public function view()
    {
        return $this->render($this->action->id, [
            'model' => $this->model,
        ]);
    }

    /**
     * @return array
     */
    public function actionPayValidate()
    {
        $rr = new RequestResponse();
        return $rr->ajaxValidateForm($this->model);
    }

    /**
     * @return array
     */
    public function actionValidate()
    {
        $rr = new RequestResponse();
        return $rr->ajaxValidateForm($this->model);
    }

    /**
     * @return array
     */
    public function actionPay()
    {
        $rr = new RequestResponse();

        /**
         * @var $model ShopOrder;
         */
        $model = $this->model;
        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            $rr->success = true;

            if ($model->payed != "Y") {
                $model->processNotePayment();
            } else {
                if (\Yii::$app->request->post('payment-close') == 1) {
                    $model->processCloseNotePayment();
                }
            }

            return $rr;
        }
    }

    /**
     * @return array
     */
    public function actionSave()
    {
        $rr = new RequestResponse();

        /**
         * @var $model ShopOrder;
         */
        $model = $this->model;
        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            $rr->success = true;
        } else {
            $rr->success = false;
            $rr->message = "Ошибка сохранения: ".print_r($model->errors, true);
        }

        return $rr;
    }


    /**
     * @return array
     */
    public function actionCreateOrderFuserSave()
    {
        $rr = new RequestResponse();

        $model = null;
        if ($id = \Yii::$app->request->get('shopFuserId')) {
            $model = ShopFuser::findOne($id);
        }

        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            $rr->success = true;
            return $rr;
        } else {
            $rr->success = false;
            print_r($model->getErrors());
            die;
            $rr->message = implode(',', $model->getFirstError());
            return $rr;
        }
    }


    /**
     * @return array
     */
    public function actionCreateOrderAddProduct()
    {
        $rr = new RequestResponse();

        $shopFuser = null;
        if ($id = \Yii::$app->request->get('shopFuserId')) {
            $shopFuser = ShopFuser::findOne($id);
        }


        if ($rr->isRequestAjaxPost()) {
            $product_id = \Yii::$app->request->post('product_id');
            $quantity = \Yii::$app->request->post('quantity');

            /**
             * @var ShopProduct $product
             */
            $product = ShopProduct::find()->where(['id' => $product_id])->one();

            if (!$product) {
                $rr->message = \Yii::t('skeeks/shop/app', 'This product is not found, it may be removed.');
                return (array)$rr;
            }

            $shopBasket = ShopBasket::find()->where([
                'fuser_id'   => $shopFuser->id,
                'product_id' => $product_id,
                'order_id'   => null,
            ])->one();

            if (!$shopBasket) {
                $shopBasket = new ShopBasket([
                    'fuser_id'   => $shopFuser->id,
                    'product_id' => $product->id,
                    'quantity'   => 0,
                ]);
            }

            $shopBasket->quantity = $shopBasket->quantity + $quantity;


            if (!$shopBasket->recalculate()->save()) {
                $rr->success = false;
                $rr->message = \Yii::t('skeeks/shop/app', 'Failed to add item to cart');
            } else {
                $rr->success = true;
                $rr->message = \Yii::t('skeeks/shop/app', 'Item added to cart');
            }

            $shopFuser->link('site', \Yii::$app->skeeks->site);
            $rr->data = $shopFuser->toArray([], $shopFuser->extraFields());
            return (array)$rr;
        } else {
            return $this->goBack();
        }
    }

    /**
     * @return array
     */
    public function actionUpdateOrderAddProduct()
    {
        $rr = new RequestResponse();

        if ($this->model) {
            $model = $this->model;
        }


        if ($rr->isRequestAjaxPost()) {
            $product_id = \Yii::$app->request->post('product_id');
            $quantity = \Yii::$app->request->post('quantity');

            /**
             * @var ShopProduct $product
             */
            $product = ShopProduct::find()->where(['id' => $product_id])->one();

            if (!$product) {
                $rr->message = \Yii::t('skeeks/shop/app', 'This product is not found, it may be removed.');
                return (array)$rr;
            }

            $shopBasket = ShopBasket::find()->where([
                'shop_order_id'   => $model->id,
                'shop_product_id' => $product_id,
            ])->one();

            if (!$shopBasket) {
                $shopBasket = new ShopBasket([
                    'shop_order_id'   => $model->id,
                    'shop_product_id' => $product->id,
                    'quantity'        => 0,
                ]);
            }

            $shopBasket->quantity = $shopBasket->quantity + $quantity;


            if (!$shopBasket->recalculate()->save()) {
                $rr->success = false;
                $rr->message = \Yii::t('skeeks/shop/app', 'Failed to add item to cart');
            } else {
                $rr->success = true;
                $rr->message = \Yii::t('skeeks/shop/app', 'Item added to cart');
            }

            $rr->data = $model->toArray([], $model->extraFields());
            return (array)$rr;
        } else {
            return $this->goBack();
        }
    }


    public function createOrder()
    {
        $cmsUser = null;
        if ($userId = \Yii::$app->request->get('cmsUserId')) {
            $cmsUser = CmsUser::findOne($userId);
        }

        if ($cmsUser) {
            /**
             * @var $shopFuser ShopFuser
             */
            $shopFuser = ShopFuser::getInstanceByUser($cmsUser);
            $model = $shopFuser;

            $rr = new RequestResponse();

            if (\Yii::$app->request->isAjax && !\Yii::$app->request->isPjax) {
                $model->scenario = ShopFuser::SCENARIO_CREATE_ORDER;
                return $rr->ajaxValidateForm($model);
            }

            if ($rr->isRequestPjaxPost()) {
                try {
                    if ($model->load(\Yii::$app->request->post()) && $model->save()) {

                        $model->scenario = ShopFuser::SCENARIO_CREATE_ORDER;

                        if ($model->validate()) {
                            $order = ShopOrder::createOrderByFuser($model);

                            if (!$order->isNewRecord) {
                                \Yii::$app->getSession()->setFlash('success',
                                    \Yii::t('skeeks/shop/app', 'The order #{order_id} created successfully',
                                        ['order_id' => $order->id])
                                );

                                if (\Yii::$app->request->post('submit-btn') == 'apply') {
                                    return $this->redirect(
                                        UrlHelper::constructCurrent()->setCurrentRef()->enableAdmin()->setRoute($this->modelDefaultAction)->normalizeCurrentRoute()
                                            ->addData([$this->requestPkParamName => $order->id])
                                            ->toString()
                                    );
                                } else {
                                    return $this->redirect(
                                        $this->url
                                    );
                                }


                            } else {
                                throw new Exception(\Yii::t('skeeks/shop/app',
                                        'Incorrect data of the new order').": ".array_shift($order->getFirstErrors()));
                            }

                        } else {
                            throw new Exception(\Yii::t('skeeks/shop/app',
                                    'Not enogh data for ordering').": ".array_shift($model->getFirstErrors()));
                        }
                    } else {
                        throw new Exception(\Yii::t('skeeks/shop/app', 'Could not save'));
                    }
                } catch (\Exception $e) {
                    \Yii::$app->getSession()->setFlash('error', $e->getMessage());
                }

            }

            return $this->render($this->action->id, [
                'cmsUser'   => $cmsUser,
                'shopFuser' => $model,
            ]);
        } else {
            return $this->render($this->action->id."-select-user");
        }
    }
}
