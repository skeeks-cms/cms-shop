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
class AdminShopStoreProductMoveController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = "Движение товара";
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopStoreProductMove::class;

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

            'create' => new UnsetArrayValue(),
            'update' => new UnsetArrayValue(),
            'delete' => new UnsetArrayValue(),
            'delete-multi' => new UnsetArrayValue(),
            'index' => [

                "filters"         => false,
                "backendShowings" => false,
                'grid'            => [
                    'on init'        => function (Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;
                        $query->joinWith("shopStoreDocMove as shopStoreDocMove");
                        $query->andWhere(["shopStoreDocMove.shop_store_id" => ShopStore::find()->isSupplier(false)->cmsSite()->select(['id'])]);
                        //$query->andWhere(['is_supplier' => 0]);
                    },
                    'defaultOrder'   => [
                        'created_at' => SORT_DESC,
                    ],
                    'visibleColumns' => [

                        //'checkbox',
                        //'actions',

                        //'id',
                        //'id',
                        'created_at',
                        'quantity',
                        'price',

                        'shop_store_doc_move_id',
                        'store',
                    ],
                    'columns'        => [

                        'created_at' => [
                            'class'      => DateTimeColumnData::class,
                            'view_type'      => DateTimeColumnData::VIEW_DATE,
                        ],

                        'quantity' => [

                            'value'         => function(ShopStoreProductMove $shopStoreProductMove) {
                                return "<b>{$shopStoreProductMove->quantity}</b>";
                            },

                        ],
                        'shop_store_doc_move_id' => [

                            'value'         => function(ShopStoreProductMove $shopStoreProductMove) {
                                return \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::widget([
                                    'controllerId'            => 'shop/admin-shop-store-doc-move',
                                    'tag'                     => 'span',
                                    'content' => $shopStoreProductMove->shopStoreDocMove->asText,
                                    'defaultOptions'          => [
                                        'class' => 'd-flex',
                                        'style' => 'line-height: 1.1; cursor: pointer;',
                                    ],
                                    'modelId'                 => $shopStoreProductMove->shopStoreDocMove->id,
                                    'isRunFirstActionOnClick' => true,
                                ]);
                            },

                        ],

                        'store' => [
                            'label' => 'Магазин',

                            'value'         => function(ShopStoreProductMove $shopStoreProductMove) {
                                return $shopStoreProductMove->shopStoreDocMove->shopStore->name;
                            },

                        ]


                    ],
                ],
            ],

        ]);
    }

}
