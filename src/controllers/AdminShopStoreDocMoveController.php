<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\actions\BackendGridModelRelatedAction;
use skeeks\cms\backend\actions\BackendModelAction;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\grid\DefaultActionColumn;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\grid\UserColumnData;
use skeeks\cms\helpers\Image;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\rbac\CmsManager;
use skeeks\cms\shop\models\ShopCachebox;
use skeeks\cms\shop\models\ShopCashebox;
use skeeks\cms\shop\models\ShopCloudkassa;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopProduct;
use skeeks\cms\shop\models\ShopProductPrice;
use skeeks\cms\shop\models\ShopStore;
use skeeks\cms\shop\models\ShopStoreDocMove;
use skeeks\cms\shop\models\ShopStoreProduct;
use skeeks\cms\shop\models\ShopStoreProductMove;
use skeeks\cms\shop\models\ShopTypePrice;
use skeeks\cms\shop\store\StoreUrlRule;
use skeeks\cms\Skeeks;
use skeeks\cms\widgets\AjaxFileUploadWidget;
use skeeks\cms\widgets\AjaxSelectModel;
use skeeks\cms\widgets\GridView;
use skeeks\cms\ya\map\widgets\YaMapDecodeInput;
use skeeks\cms\ya\map\widgets\YaMapInput;
use skeeks\yii2\ckeditor\CKEditorWidget;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\HtmlBlock;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextareaField;
use skeeks\yii2\form\fields\WidgetField;
use yii\base\Event;
use yii\base\Exception;
use yii\bootstrap\Alert;
use yii\data\Pagination;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\UnsetArrayValue;
use yii\helpers\Url;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminShopStoreDocMoveController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = "Движение товара";
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopStoreDocMove::class;

        $this->generateAccessActions = false;
        $this->permissionName = CmsManager::PERMISSION_ROLE_ADMIN_ACCESS;

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
            ],


            "update-attribute" => [

                'class'     => BackendModelAction::class,
                'isVisible' => false,
                'callback'  => [$this, 'actionUpdateAttribute'],
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
                        $query->andWhere(["shop_store_id" => ShopStore::find()->isSupplier(false)->cmsSite()->select(['id'])]);
                        //$query->andWhere(['is_supplier' => 0]);
                    },
                    'defaultOrder'   => [
                        'created_at' => SORT_DESC,
                    ],
                    'visibleColumns' => [

                        //'checkbox',
                        'actions',

                        //'id',
                        //'id',
                        'doc_type',

                        'created_at',
                        'created_by',
                        'shop_store_id',

                        'number_products',
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

    public function actionAdd()
    {
        $model = new ShopStoreDocMove();
        $model->is_active = 0;
        $model->doc_type = \Yii::$app->request->get("doc_type");

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
                
                if (!$model->save()) {
                    if ($model->getFirstErrors()) {
                        $errors = $model->getFirstErrors();
                        $error = array_shift($errors);
                        throw new \yii\base\Exception($error);
                    }
                }

                $t->commit();

                $rr->data['view_url'] = Url::to(['view', 'pk' => $model->id]);
                $rr->message = "Документ добавлен";
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

    public function updateFields($action)
    {
        $action->model->load(\Yii::$app->request->get());

        return [
            'main'           => [
                'class'  => FieldSet::class,
                'name'   => \Yii::t('skeeks/shop/app', 'Main'),
                'fields' => [
                    'is_active' => [
                        'class'        => BoolField::class,
                        'allowNull'        => false,
                    ],
                    'comment' => [
                        'class' => TextareaField::class
                    ],
                ],
            ],
        ];
    }
    
    public function actionCreateCorrection()
    {
        Skeeks::unlimited();

        $rr = new RequestResponse();

        $t = \Yii::$app->db->beginTransaction();

        try {
            
            $qStores = \skeeks\cms\shop\models\ShopStore::find()->isSupplier(false)->cmsSite();
            foreach ($qStores->each(10) as $store)
            {
                $productsQuery = \skeeks\cms\shop\models\ShopStoreProduct::find()
                    ->select([\skeeks\cms\shop\models\ShopStoreProduct::tableName().".*", 'total_quantity' => new \yii\db\Expression("if(sum(shopStoreProductMoves.quantity), sum(shopStoreProductMoves.quantity), 0)")])
                    ->andWhere(['shop_store_id' => $store->id])
                    //->andWhere(["!=", \skeeks\cms\shop\models\ShopStoreProduct::tableName().'.quantity', 0])
                    ->andHaving([
                        "!=", \skeeks\cms\shop\models\ShopStoreProduct::tableName().'.quantity', new \yii\db\Expression("total_quantity")
                    ])
                    //->joinWith("shopStoreProductMoves as shopStoreProductMoves")
                    ->leftJoin(["shopStoreProductMoves" => \skeeks\cms\shop\models\ShopStoreProductMove::tableName()], [
                        "shopStoreProductMoves.shop_store_product_id" => new \yii\db\Expression(\skeeks\cms\shop\models\ShopStoreProduct::tableName() . ".id"),
                        "shopStoreProductMoves.is_active" => "1",
                    ])
                    ->groupBy([\skeeks\cms\shop\models\ShopStoreProduct::tableName().".id"]);
                


                //Только если есть товары с расхождением
                if ($productsQuery->count()) {

                    $doc = new ShopStoreDocMove();
                    $doc->doc_type = ShopStoreDocMove::DOCTYPE_CORRECTION;
                    $doc->shop_store_id = $store->id;
                    $doc->comment = "Начальная корректировка";
                    $doc->is_active = 0;
                    if (!$doc->save()) {
                        throw new Exception("Ошибка: " . print_r($doc->errors, true));
                    }
                    /**
                     * @var ShopStoreProduct $shopStoreProduct
                     */
                    foreach ($productsQuery->each(10) as $shopStoreProduct)
                    {
                        $newValue = $shopStoreProduct->quantity - (float) $shopStoreProduct->raw_row['total_quantity'];
                        $move = new ShopStoreProductMove();
                        $move->is_active = 1;
                        $move->quantity = $newValue;
                        $move->shop_store_doc_move_id = $doc->id;
                        $move->price = (float) ($shopStoreProduct->shopProduct && $shopStoreProduct->shopProduct->baseProductPrice ? $shopStoreProduct->shopProduct->baseProductPrice->price : 0);
                        $move->product_name = $shopStoreProduct->productName;
                        $move->shop_store_product_id = (int) $shopStoreProduct->id;
                        if (!$move->save()) {
                            throw new Exception("Ошибка: " . print_r($move->errors, true));
                        }
                    }
                    
                    $doc->is_active = 1;
                    if (!$doc->save()) {
                        throw new Exception("Ошибка: " . print_r($doc->errors, true));
                    }
                }
            }

            $t->commit();


            $rr->success = true;
        } catch (\Exception $exception) {
            $t->rollBack();
            $rr->success = false;
            $rr->message = $exception->getMessage();
        }

        return $rr;
    }





    /**
     * Добавить товар
     *
     * @return array|\yii\web\Response
     */
    public function actionAddProduct()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {

            $product_id = \Yii::$app->request->post('product_id');
            $quantity = \Yii::$app->request->post('quantity');

            /**
             * @var $shopStoreDocMove ShopStoreDocMove
             */
            $shopStoreDocMove = $this->model;

            /**
             * @var ShopProduct $product
             */
            $product = ShopProduct::find()->where(['id' => $product_id])->one();

            if (!$product) {
                $rr->message = \Yii::t('skeeks/shop/app', 'This product is not found, it may be removed.');
                return (array)$rr;
            }

            if ($product->isOffersProduct) {
                $rr->message = \Yii::t('skeeks/shop/app', 'Этот товар является общим, и не может быть добавлен в корзину.');
                return (array)$rr;
            }

            if ($product->measure_ratio > 1) {
                if ($quantity % $product->measure_ratio != 0) {
                    $quantity = $product->measure_ratio;
                }
            }

            $shopStoreProduct = $product->getStoreProduct($shopStoreDocMove->shopStore);
            if (!$shopStoreProduct) {
                $shopStoreProduct = new ShopStoreProduct();
                $shopStoreProduct->shop_store_id = $shopStoreDocMove->shop_store_id;
                $shopStoreProduct->shop_product_id = $product->id;
                if (!$shopStoreProduct->save()) {
                    $rr->message = "Не удалось добавить товар";
                    return (array)$rr;
                }
            }

            $productMove = $shopStoreDocMove->getShopStoreProductMoves()->andWhere(['shop_store_product_id' => $shopStoreProduct->id])->one();
            if (!$productMove) {
                $productMove = new ShopStoreProductMove();
                $productMove->shop_store_product_id = $shopStoreProduct->id;
                $productMove->shop_store_doc_move_id = $shopStoreDocMove->id;
                $productMove->product_name = $product->cmsContentElement->productName;


                $typePurchasePrice = null;
                if ($shopStoreDocMove->doc_type == ShopStoreDocMove::DOCTYPE_POSTING) {
                    $typePurchasePrice = ShopTypePrice::find()->cmsSite()->andWhere(['is_default' => 1])->one();
                } elseif ($shopStoreDocMove->doc_type == ShopStoreDocMove::DOCTYPE_WRITEOFF) {
                    $typePurchasePrice = ShopTypePrice::find()->cmsSite()->andWhere(['is_purchase' => 1])->one();
                }

                $price = null;
                if ($typePurchasePrice) {
                    $price = $product->getPrice($typePurchasePrice);
                }

                if (!$price || !(float)$price->price) {
                    $typePrice = ShopTypePrice::find()->cmsSite()->andWhere(['is_default' => 1])->one();
                    if ($typePrice) {
                        $price = $product->getPrice($typePrice);
                    }
                }
                /**
                 * @var $price ShopProductPrice
                 */
                if ($price) {
                    $productMove->price = $price->price;
                }
                $productMove->is_active = 0;
                if (!$productMove->save()) {
                    $rr->message = "Не удалось обновить товар";
                    return (array)$rr;
                }
            }


            $productMove->quantity = abs($productMove->quantity) + abs($quantity);
            if ($product->measure_ratio_min > $productMove->quantity) {
                $productMove->quantity = $product->measure_ratio_min;
            }

            $int = round($productMove->quantity / $product->measure_ratio);
            $productMove->quantity = $int * $product->measure_ratio;

            if ($product->measure_ratio_min > $productMove->quantity) {
                $productMove->quantity = $product->measure_ratio_min;
            }
            
            if ($shopStoreDocMove->doc_type == ShopStoreDocMove::DOCTYPE_WRITEOFF) {
                $productMove->quantity = $productMove->quantity * -1;
            }

            if (!$productMove->save()) {
                $rr->message = "Не удалось обновить товар";
            }

            return (array)$rr;
        } else {
            return $this->goBack();
        }
    }



    /**
     * Добавить товар
     *
     * @return array|\yii\web\Response
     */
    public function actionRemoveItem()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {

            $itemId = \Yii::$app->request->post('id');

            /**
             * @var $shopStoreDocMove ShopStoreDocMove
             */
            $shopStoreDocMove = $this->model;

            $docMove = $shopStoreDocMove->getShopStoreProductMoves()->andWhere(['id' => $itemId])->one();

            if (!$docMove->delete()) {
                $rr->message = "Не удалось удалить позицию: " . print_r($docMove->errors, true);
            } else {
                $rr->success = true;
            }


            return (array)$rr;
        } else {
            return $this->goBack();
        }
    }

    /**
     * Добавить товар
     *
     * @return array|\yii\web\Response
     */
    public function actionApproveDoc()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {

            $itemId = \Yii::$app->request->post('id');

            $t = \Yii::$app->db->beginTransaction();

            try {

                /**
                 * @var $shopStoreDocMove ShopStoreDocMove
                 */
                $shopStoreDocMove = $this->model;
                $shopStoreDocMove->is_active = 1;

                foreach ($shopStoreDocMove->shopStoreProductMoves as $shopStoreProductMove)
                {
                    //Активировать количество в текущей позиции
                    $shopStoreProductMove->is_active = 1;
                    if (!$shopStoreProductMove->save()) {
                        throw new Exception("Ошибка: " . print_r($shopStoreProductMove->errors, true));
                    }

                    //Если есть товар ему нужно обновить количество
                    if ($shopStoreProduct = $shopStoreProductMove->shopStoreProduct) {
                        $data = $shopStoreProduct->getShopStoreProductMoves()->andWhere(['is_active' => 1])->select(['quantity' => new Expression("sum(quantity)")])->asArray()->one();
                        $shopStoreProduct->quantity = (float) $data['quantity'];
                        $shopStoreProduct->isAllowCorrection = false;

                        if (!$shopStoreProduct->save()) {
                            $rr->message = "Не удалось сохранить количество в товаре: " . print_r($shopStoreProduct->errors, true);
                        }
                    }
                }

                if (!$shopStoreDocMove->save()) {
                    $rr->message = "Не удалось мохранить документ: " . print_r($shopStoreDocMove->errors, true);
                } else {
                    $rr->success = true;
                }


                $t->commit();

            } catch (\Exception $exception) {
                $t->rollBack();
                throw $exception;
            }

            return (array)$rr;
        } else {
            return $this->goBack();
        }
    }


    /**
     * Добавить товар
     *
     * @return array|\yii\web\Response
     */
    public function actionNoApproveDoc()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {

            $itemId = \Yii::$app->request->post('id');

            $t = \Yii::$app->db->beginTransaction();

            try {

                /**
                 * @var $shopStoreDocMove ShopStoreDocMove
                 */
                $shopStoreDocMove = $this->model;
                $shopStoreDocMove->is_active = 0;

                foreach ($shopStoreDocMove->shopStoreProductMoves as $shopStoreProductMove)
                {
                    //Активировать количество в текущей позиции
                    $shopStoreProductMove->is_active = 0;
                    if (!$shopStoreProductMove->save()) {
                        throw new Exception("Ошибка: " . print_r($shopStoreProductMove->errors, true));
                    }

                    //Если есть товар ему нужно обновить количество
                    if ($shopStoreProduct = $shopStoreProductMove->shopStoreProduct) {
                        $data = $shopStoreProduct->getShopStoreProductMoves()->andWhere(['is_active' => 1])->select(['quantity' => new Expression("sum(quantity)")])->asArray()->one();
                        $shopStoreProduct->quantity = (float) $data['quantity'];
                        $shopStoreProduct->isAllowCorrection = false;

                        if (!$shopStoreProduct->save()) {
                            $rr->message = "Не удалось сохранить количество в товаре: " . print_r($shopStoreProduct->errors, true);
                        }
                    }
                }

                if (!$shopStoreDocMove->save()) {
                    $rr->message = "Не удалось сохранить документ: " . print_r($shopStoreDocMove->errors, true);
                } else {
                    $rr->success = true;
                }


                $t->commit();

            } catch (\Exception $exception) {
                $t->rollBack();
                throw $exception;
            }

            return (array)$rr;
        } else {
            return $this->goBack();
        }
    }


    /**
     * Добавить товар
     *
     * @return array|\yii\web\Response
     */
    public function actionUpdateItem()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {

            $itemId = \Yii::$app->request->post('id');

            /**
             * @var $shopStoreDocMove ShopStoreDocMove
             * @var $shopStoreProductMove ShopStoreProductMove
             */
            $shopStoreDocMove = $this->model;

            $shopStoreProductMove = $shopStoreDocMove->getShopStoreProductMoves()->andWhere(['id' => $itemId])->one();

            if (\Yii::$app->request->post('quantity')) {
                $shopStoreProductMove->quantity = (float) \Yii::$app->request->post('quantity');
                
                if ($shopStoreDocMove->doc_type == ShopStoreDocMove::DOCTYPE_WRITEOFF) {
                    $shopStoreProductMove->quantity = $shopStoreProductMove->quantity * -1;
                }
                
            }
            if (\Yii::$app->request->post('price')) {
                $shopStoreProductMove->price = (float) \Yii::$app->request->post('price');
            }


            if (!$shopStoreProductMove->save()) {
                $rr->message = "Не удалось обновить позицию: " . print_r($docMove->errors, true);
            } else {
                $rr->success = true;
            }

            return (array)$rr;
        } else {
            return $this->goBack();
        }
    }



    /**
     * Это бэкенд для поиска товаров
     *
     * @return RequestResponse
     */
    public function actionProducts()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {
            \Yii::$app->shop->backendShopStore;
            \Yii::$app->skeeks->site;
            $q = \Yii::$app->request->post("q");
            $page = \Yii::$app->request->post("page", 0);
            $shopStoreDocMove = $this->model;

            $query = ShopCmsContentElement::find()
                ->andWhere([
                    'shopProduct.product_type' => [
                        ShopProduct::TYPE_SIMPLE,
                        ShopProduct::TYPE_OFFER
                    ]
                ])
                ->from(['cce' => ShopCmsContentElement::tableName()])
                ->innerJoinWith("shopProduct as shopProduct")
                ->groupBy(["cce.id"]);

            if ($q) {
                $q = trim($q);
                $query->joinWith("shopProduct.shopProductBarcodes as barcodes");
                $query->andWhere([
                    'or',
                    ['like', 'cce.name', $q],
                    ['=', 'cce.id', $q],
                    ['=', 'barcodes.value', $q],
                ]);
                $query->groupBy("shopProduct.id");
            }

            $countQuery = clone $query;
            $totalCount = $countQuery->count();


            if ($totalCount) {

                $pagination = new Pagination(['totalCount' => $totalCount, 'defaultPageSize' => 20]);
                $pagination->setPage($page);
                $models = $query->offset($pagination->offset)->limit($pagination->limit);


                $content = '';
                foreach ($query->each(10) as $element) {
                    $content .= $this->renderPartial('_product', [
                        'model' => $element,
                        'shopStoreDocMove' => $shopStoreDocMove,
                    ]);
                }
                $hasNextPage = (bool) ($pagination->page < ($pagination->pageCount-1));
                $nexPage = $pagination->page;
                if ($hasNextPage) {
                    $nexPage = $pagination->page + 1;
                }

                if ($hasNextPage) {
                    $content .= "<div class='sx-more'><button class='btn btn-default btn-block sx-btn-next-page' data-next-page='{$nexPage}' data-load-text='Ожидайте! Идет загрузка...'>Показать еще</button></div>";
                }

                $data['content'] = $content;
                $data['pagination'] = [
                    'offset'      => $pagination->offset,
                    'totalCount'  => (int)$pagination->totalCount,
                    'page'        => $pagination->page,
                    'pageSize'    => $pagination->pageSize,
                    'pageCount'   => $pagination->pageCount,
                    'hasNextPage' => $hasNextPage,
                    'nextPage' => $nexPage,
                ];
            } else {
                $content = "<div class='sx-not-found-products'><h1>Товар не найден</h1></div>";
                $data['content'] = $content;
            }


            $rr->success = true;
            $rr->data = $data;

        }

        return $rr;
    }





    /**
     *
     */
    public function actionUpdateAttribute()
    {
        $rr = new RequestResponse();
        /**
         * @var $model ShopStoreDocMove
         */
        $model = $this->model;

        if ($rr->isRequestAjaxPost()) {

            $attribute = \Yii::$app->request->post("attribute");
            $value = \Yii::$app->request->post("value");

            try {

                if ($model->load(\Yii::$app->request->post())) {
                    if (!$model->save()) {
                        throw new \yii\base\Exception("Ошибка сохранения данных: ".print_r($model->errors, true));
                    }
                }

                $rr->message = "Обновлено";
                $rr->success = true;

            } catch (\Exception $exception) {
                $rr->message = $exception->getMessage();
                $rr->success = false;
            }
        }

        return $rr;
    }



    /**
     * Добавить товар
     *
     * @return array|\yii\web\Response
     */
    public function actionAddProductBarcode()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {

            $barcode = \Yii::$app->request->post('barcode');
            if (!$barcode) {
                return $rr;
            }

            $query = ShopCmsContentElement::find()
                ->andWhere(['cce.cms_site_id' => \Yii::$app->skeeks->site->id])
                ->from(['cce' => ShopCmsContentElement::tableName()])
                ->innerJoinWith("shopProduct as shopProduct")
                ->groupBy(["cce.id"]);

            if ($barcode) {
                $barcode = trim($barcode);
                $query->joinWith("shopProduct.shopProductBarcodes as barcodes");
                $query->andWhere(
                    ['=', 'barcodes.value', $barcode],
                );
                $query->groupBy("shopProduct.id");
            }

            $countQuery = clone $query;
            $totalCount = $countQuery->count();

            $product = [];
            if ($totalCount == 1) {
                $product = $query->one()->toArray();
            }
            $data["total"] = (int) $totalCount;
            $data["product"] = $product;

            $rr->data = $data;
            $rr->success = true;

            return (array)$rr;
        } else {
            return $this->goBack();
        }
    }
}
