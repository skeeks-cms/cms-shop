<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\actions\backend\BackendModelMultiActivateAction;
use skeeks\cms\actions\backend\BackendModelMultiDeactivateAction;
use skeeks\cms\backend\actions\BackendModelAction;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\grid\DefaultActionColumn;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\grid\ImageColumn;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\components\DeliveryHandlerComponent;
use skeeks\cms\shop\deliveries\BoxberryDeliveryHandler;
use skeeks\cms\shop\models\ShopDelivery;
use skeeks\cms\shop\models\ShopMarketplace;
use skeeks\cms\widgets\AjaxFileUploadWidget;
use skeeks\cms\widgets\formInputs\comboText\ComboTextInputWidget;
use skeeks\yii2\form\Builder;
use skeeks\yii2\form\elements\HtmlColBegin;
use skeeks\yii2\form\elements\HtmlColEnd;
use skeeks\yii2\form\elements\HtmlRowBegin;
use skeeks\yii2\form\elements\HtmlRowEnd;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\WidgetField;
use yii\base\Event;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\UnsetArrayValue;
use yii\helpers\Url;

/**
 * Class AdminTaxController
 * @package skeeks\cms\shop\controllers
 */
class AdminMarketplaceController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Marketplace');
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopMarketplace::class;

        $this->generateAccessActions = false;

        parent::init();
    }


    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [

            "view" => [
                'class'    => BackendModelAction::class,
                'priority' => 80,
                'name'     => 'Просмотр',
                'icon'     => 'fas fa-info-circle',
                'isOpenNewWindow' => false
            ],

            "stores" => [
                'class'    => BackendModelAction::class,
                'priority' => 85,
                'name'     => 'Склады',
                'icon'     => 'fas fa-info-circle',
                'isOpenNewWindow' => false
            ],

            "products" => [
                'class'    => BackendModelAction::class,
                'priority' => 90,
                'name'     => 'Товары',
                'icon'     => 'fas fa-info-circle',
                'isOpenNewWindow' => false
            ],

            "orders" => [
                'class'    => BackendModelAction::class,
                'priority' => 100,
                'name'     => 'Заказы',
                'icon'     => 'fas fa-info-circle',
                'isOpenNewWindow' => false
            ],

            "sells" => [
                'class'    => BackendModelAction::class,
                'priority' => 100,
                'name'     => 'Продажи',
                'icon'     => 'fas fa-info-circle',
                'isOpenNewWindow' => false
            ],

            "report" => [
                'class'    => BackendModelAction::class,
                'priority' => 100,
                'name'     => 'Отчет по продажам',
                'icon'     => 'fas fa-info-circle',
                'isOpenNewWindow' => false
            ],


            /*"add" => [
                'class'    => BackendModelAction::class,
                'priority' => 80,
                'name'     => 'Создать документ',
            ],*/

            'create' => new UnsetArrayValue(),
            'update' => new UnsetArrayValue(),
            /*"update" => [
                'fields' => [$this, 'updateFields'],

            ],*/
            'delete-multi' => new UnsetArrayValue(),
            'index' => [
                'on beforeRender' => function (Event $e) {

                    $e->content = $this->renderPartial("_index_btns");
                    /*$e->content = Alert::widget([
                        'closeButton' => false,
                        'options'     => [
                            'class' => 'alert-default',
                        ],

                        'body' => <<<HTML
<a href="#" class="btn btn-primary">Создать движение</a>
HTML
                        ,
                    ]);*/

                },
                "filters"         => false,
                "backendShowings" => false,
                'grid'            => [
                    'on init'        => function (Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;
                        //$query->andWhere(['is_supplier' => 0]);
                    },
                    'defaultOrder'   => [
                        'created_at' => SORT_DESC,
                    ],
                    'visibleColumns' => [

                        //'checkbox',
                        'actions',
                        'name',

                        //'id',
                        //'id',
                        'is_active',
                    ],
                    'columns'        => [

                        'is_active' => [
                            'class'      => BooleanColumn::class,
                            'trueValue'  => 1,
                            'falseValue' => 1,
                        ],
                        'created_at' => [
                            'class'      => DateTimeColumnData::class,
                            'view_type'      => DateTimeColumnData::VIEW_DATE,
                        ],
                        'created_by' => [
                            'class'      => UserColumnData::class,
                        ],


                        'name' => [
                            'class'         => DefaultActionColumn::class,
                            'viewAttribute' => 'asText',
                        ],


                        'doc_type' => [
                            'value'         => function(ShopStoreDocMove $shopStoreDocMove, $key, $index) {

                if (!$shopStoreDocMove->is_active) {
                    \Yii::$app->view->registerJs(<<<JS
$('tr[data-key={$key}]').addClass('sx-tr-no-active');
JS
                                        );
                }


                                        \Yii::$app->view->registerCss(<<<CSS
tr.sx-tr-no-active td
{
opacity: 0.2;
}
tr.sx-tr-no-active:hover td
{
opacity: 1;
}
CSS
                                        );




                                $result = [];
                                $result[] = \yii\helpers\Html::a($shopStoreDocMove->asText, "#", [
                                    'class' => "sx-trigger-action",
                                ]);
                                if ($shopStoreDocMove->comment) {
                                    $result[] = "<small style='color: gray;'>{$shopStoreDocMove->comment}</small>";
                                }
                                return implode("<br />", $result);
                            }
                        ],

                        'shop_order_id' => [
                            'value'         => function(ShopStoreDocMove $shopStoreDocMove) {
                                if ($shopStoreDocMove->shopOrder) {
                                    return \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::widget([
                                        'controllerId'            => '/shop/admin-order',
                                        'modelId'                 => $shopStoreDocMove->shopOrder->id,
                                        'content'                 => $shopStoreDocMove->shopOrder->asText,
                                        'isRunFirstActionOnClick' => true,
                                        'options'                 => [
                                            'class' => 'btn btn-xs btn-default',
                                            //'style' => 'cursor: pointer; border-bottom: 1px dashed;',
                                        ],
                                    ]);
                                } else {
                                    return '';
                                }
                            },
                        ],

                        'shop_store_id' => [
                            'value'         => function(ShopStoreDocMove $shopStoreDocMove) {
                                if ($shopStoreDocMove->shopStore) {
                                    return \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::widget([
                                        'controllerId'            => '/shop/admin-shop-store',
                                        'modelId'                 => $shopStoreDocMove->shopStore->id,
                                        'content'                 => $shopStoreDocMove->shopStore->asText,
                                        'isRunFirstActionOnClick' => true,
                                        'options'                 => [
                                            'class' => 'btn btn-xs btn-default',
                                            //'style' => 'cursor: pointer; border-bottom: 1px dashed;',
                                        ],
                                    ]);
                                } else {
                                    return '';
                                }
                            },
                        ],

                        'number_products' => [

                            'label' => 'Позиций',
                            'attribute' => 'number_products',

                            'value'         => function(ShopStoreDocMove $shopStoreDocMove) {
                                return $shopStoreDocMove->raw_row['number_products'];
                            },

                            'beforeCreateCallback' => function (GridView $grid) {
                                /**
                                 * @var $query ActiveQuery
                                 */
                                $query = $grid->dataProvider->query;

                                $subQuery = ShopStoreProductMove::find()->select([new Expression("count(1)")])->where(
                                    ['shop_store_doc_move_id' => new Expression(ShopStoreDocMove::tableName() . ".id")],
                                );

                                $query->addSelect([
                                    'number_products' => $subQuery,
                                ]);


                                $grid->sortAttributes["number_products"] = [
                                    'asc'  => ['number_products' => SORT_ASC],
                                    'desc' => ['number_products' => SORT_DESC],
                                ];
                            },
                        ],

                    ],
                ],
            ],

        ]);
    }

    public function actionView() {

        return $this->render("view", [
            'model' => $this->model,
        ]);
    }

    public function actionAdd()
    {
        $model = new ShopMarketplace();
        $model->is_active = 1;
        $marketplace = \Yii::$app->request->get("marketplace");
        if (!$marketplace) {
            return $this->redirect("index");
        }
        $model->marketplace = \Yii::$app->request->get("marketplace");

        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {

            $t = \Yii::$app->db->beginTransaction();

            try {


                if (!$model->load(\Yii::$app->request->post()) || !$model->validate()) {
                    $message = "Проверьте корректность данных";

                    $errors = $model->getFirstErrors();
                    if ($errors) {
                        $error = array_shift($errors);
                        $message = $error;
                    }

                    throw new \yii\base\Exception($message);
                }
                
                if ($model->wbProvider) {
                    $apiResponse = $model->wbProvider->methodContentAll();
                    if (!$apiResponse->isOk) {
                        throw new Exception("Стандартный ключ — некорректный! Ответ от API: $apiResponse->error_message");
                    }


                    $apiResponse = $model->wbProvider->methodStatSupplierSales([
                        'dateFrom' => '2019-06-20'
                    ]);
                    if (!$apiResponse->isOk) {
                        throw new Exception("Ключ статистики — некорректный! Ответ от API: $apiResponse->error_message");
                    }
                }


                if (!$model->save()) {
                    if ($model->getFirstErrors()) {
                        $errors = $model->getFirstErrors();
                        $error = array_shift($errors);
                        throw new \yii\base\Exception($error);
                    }
                }

                $t->commit();

                $rr->data['view_url'] = Url::to(['view', 'pk' => $model->id]);
                $rr->message = "Маркетплейс подключен";
                $rr->success = true;


            } catch (\Exception $exception) {
                $t->rollBack();
                $rr->success = false;
                $rr->message = $exception->getMessage();
            }


            return $rr;
        }

        return $this->render("add", [
            'model' => $model,
        ]);
    }

}
