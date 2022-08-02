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
use skeeks\cms\helpers\Image;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\rbac\CmsManager;
use skeeks\cms\shop\models\ShopCachebox;
use skeeks\cms\shop\models\ShopCashebox;
use skeeks\cms\shop\models\ShopCloudkassa;
use skeeks\cms\shop\models\ShopStore;
use skeeks\cms\shop\models\ShopStoreDocMove;
use skeeks\cms\shop\models\ShopStoreProduct;
use skeeks\cms\shop\models\ShopStoreProductMove;
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

            'create' => new UnsetArrayValue(),
            "update" => [
                'fields' => [$this, 'updateFields'],
            ],
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
                        ],


                        'name' => [
                            'class'         => DefaultActionColumn::class,
                            'viewAttribute' => 'asText',
                        ],


                        'doc_type' => [
                            'value'         => function(ShopStoreDocMove $shopStoreDocMove) {
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
                    ->andWhere(["!=", \skeeks\cms\shop\models\ShopStoreProduct::tableName().'.quantity', 0])
                    ->andHaving([
                        "!=", \skeeks\cms\shop\models\ShopStoreProduct::tableName().'.quantity', new \yii\db\Expression("total_quantity")
                    ])
                    ->joinWith("shopStoreProductMoves as shopStoreProductMoves")
                    ->groupBy([\skeeks\cms\shop\models\ShopStoreProduct::tableName().".id"]);


                //Только если есть товары с расхождением
                if ($productsQuery->count()) {

                    $doc = new ShopStoreDocMove();
                    $doc->doc_type = ShopStoreDocMove::DOCTYPE_CORRECTION;
                    $doc->shop_store_id = $store->id;
                    $doc->comment = "Начальная корректировка";
                    $doc->is_active = 1;
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

}
