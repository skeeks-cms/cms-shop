<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\helpers\Image;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsUser;
use skeeks\cms\modules\admin\actions\AdminAction;
use skeeks\cms\shop\models\ShopBasket;
use skeeks\cms\shop\models\ShopFuser;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\shop\models\ShopProduct;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

/**
 * Class AdminExtraController
 * @package skeeks\cms\shop\controllers
 */
class AdminOrderController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Orders');
        $this->modelShowAttribute = "id";
        $this->modelClassName = ShopOrder::class;

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
                    'defaultOrder' => [
                        'is_created' => SORT_DESC,
                        'updated_at' => SORT_DESC,
                    ],

                    'visibleColumns' => [
                        'checkbox',
                        'actions',
                        'id',

                        'updated_at',

                        'shop_order_status_id',

                        'payed_at',
                        'canceled_at',

                        'shop_buyer_id',
                        'shop_pay_system_id',
                        'shop_delivery_id',

                        'items',

                        'amount',
                        'is_created',
                        'go',
                    ],
                    'columns'        => [
                        'is_created'           => [
                            'class' => BooleanColumn::class,
                        ],
                        'payed_at'             => [
                            'class' => DateTimeColumnData::class,
                        ],
                        'go'                   => [
                            'format' => "raw",
                            'value'  => function (ShopOrder $shopOrder) {
                                return \yii\helpers\Html::a('<i class="glyphicon glyphicon-arrow-right"></i>', $shopOrder->url, [
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
                        'payed_at'             => [
                            'value' => function (ShopOrder $shopOrder, $key) {
                                $reuslt = "<div>";
                                if ($shopOrder->payed_at) {
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

                                $reuslt .= $shopOrder->payed_at ? \Yii::$app->formatter->asDatetime($shopOrder->payed_at) : "-";
                                $reuslt .= "</div>";
                                return $reuslt;
                            },
                        ],
                        'updated_at'           => [
                            'class' => DateTimeColumnData::class,
                        ],
                        'items'                => [
                            'label'  => "Товары",
                            'format' => "raw",
                            'value'  => function (ShopOrder $shopOrder) {
                                if ($shopOrder->shopOrderItems) {

                                    $result = [];

                                    foreach ($shopOrder->shopOrderItems as $shopBasket) {
                                        $result[] =

                                            \yii\helpers\Html::img(Image::getSrc($shopBasket->image ? $shopBasket->image->src : null), [
                                                'height' => "50",
                                            ])
                                            .
                                            \yii\helpers\Html::a($shopBasket->name, $shopBasket->url, [
                                                'target'    => '_blank',
                                                'data-pjax' => '0',
                                            ])
                                            .
                                            <<<HTML
                 — $shopBasket->quantity $shopBasket->measure_name
HTML;

                                    }

                                    return implode('<hr style="margin: 0px;"/>', $result);
                                }
                            },
                        ],
                        'shop_order_status_id' => [
                            'value' => function (ShopOrder $shopOrder) {
                                if (!$shopOrder->shopOrderStatus) {
                                    return "-";
                                }
                                return \yii\helpers\Html::label($shopOrder->shopOrderStatus->asText, null, [
                                        'style' => "background: {$shopOrder->shopOrderStatus->color}",
                                        'class' => "label",
                                    ])."<br />".
                                    \yii\helpers\Html::tag("small",
                                        \Yii::$app->formatter->asDatetime($shopOrder->status_at)." (".\Yii::$app->formatter->asRelativeTime($shopOrder->status_at).")");
                            },
                        ],
                        'amount' => [
                            'value' => function (ShopOrder $shopOrder) {
                                $result = [];
                                $result[] = "Доставка:&nbsp;" . $shopOrder->moneyDelivery;
                                $result[] = "Скидка:&nbsp;" . $shopOrder->moneyDiscount;
                                $result[] = "Налог:&nbsp;" . $shopOrder->moneyVat;
                                return "К&nbsp;оплате:&nbsp;<b>" . $shopOrder->money . "</b><hr style='margin: 0px; padding: 0px;'/>" . implode("<br />", $result);
                            },
                        ],
                    ],

                ],
            ],

            'create' => [
                'isVisible' => false,
            ],

            'create-order' => [
                'class'    => AdminAction::class,
                'name'     => \Yii::t('skeeks/shop/app', 'Place your order'),
                "icon"     => "glyphicon glyphicon-plus",
                "callback" => [$this, 'createOrder'],
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
            return $rr;
        }
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

            $shopFuser->link('site', \Yii::$app->cms->site);
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
                'order_id'   => $model->id,
                'product_id' => $product_id,
                'fuser_id'   => null,
            ])->one();

            if (!$shopBasket) {
                $shopBasket = new ShopBasket([
                    'order_id'   => $model->id,
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
