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
use skeeks\cms\helpers\Image;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\rbac\CmsManager;
use skeeks\cms\shop\models\ShopCachebox;
use skeeks\cms\shop\models\ShopCashebox;
use skeeks\cms\shop\models\ShopCloudkassa;
use skeeks\cms\shop\models\ShopStore;
use skeeks\cms\shop\models\ShopStoreProduct;
use skeeks\cms\shop\store\StoreUrlRule;
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
use skeeks\yii2\form\fields\WidgetField;
use yii\base\Event;
use yii\bootstrap\Alert;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminShopCasheboxController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = "Кассы";
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopCashebox::class;

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

            'index' => [
                'on beforeRender' => function (Event $e) {
                    $e->content = Alert::widget([
                        'closeButton' => false,
                        'options'     => [
                            'class' => 'alert-default',
                        ],

                        'body' => <<<HTML
Для работы магазина можно добавить кассу
HTML
                        ,
                    ]);
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

                        $query->cmsSite();
                        //$query->andWhere(['is_supplier' => 0]);
                    },
                    'defaultOrder'   => [
                        'priority' => SORT_ASC,
                    ],
                    'visibleColumns' => [

                        'checkbox',
                        'actions',

                        //'id',
                        'name',
                        'shop_store_id',
                        'is_active',
                    ],
                    'columns'        => [

                        'is_active' => [
                            'class'      => BooleanColumn::class,
                            'trueValue'  => 1,
                            'falseValue' => 1,
                        ],

                        'name' => [
                            'class'         => DefaultActionColumn::class,
                            'viewAttribute' => 'asText',
                        ],

                    ],
                ],
            ],


            'payments' => [
                'class'    => BackendGridModelRelatedAction::class,
                'name'     => 'Платежи',
                'priority' => 90,
                'callback' => [$this, 'shift'],
                'icon'     => 'fas fa-credit-card',

                'controllerRoute' => "/shop/admin-payment",
                'relation'        => ['shop_cashebox_id' => 'id'],
                'on gridInit'     => function ($e) {
                    /**
                     * @var $action BackendGridModelRelatedAction
                     */
                    $action = $e->sender;
                    $action->relatedIndexAction->backendShowings = false;
                    $action->relatedIndexAction->filters = false;
                    $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];

                    ArrayHelper::removeValue($visibleColumns, 'shop_cashebox_id');
                    $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                },
            ],


            'orders' => [
                'class'    => BackendGridModelRelatedAction::class,
                'name'     => 'Продажи',
                'priority' => 90,
                'callback' => [$this, 'shift'],
                'icon'     => 'fas fa-credit-card',

                'controllerRoute' => "/shop/admin-order",
                'relation'        => ['shop_cashebox_id' => 'id'],
                'on gridInit'     => function ($e) {
                    /**
                     * @var $action BackendGridModelRelatedAction
                     */
                    $action = $e->sender;
                    $action->relatedIndexAction->backendShowings = false;
                    $action->relatedIndexAction->filters = false;
                    $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];

                    ArrayHelper::removeValue($visibleColumns, 'shop_cashebox_id');
                    $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                },
            ],


            'checks' => [
                'class'    => BackendGridModelRelatedAction::class,
                'name'     => 'Чеки',
                'priority' => 90,
                'callback' => [$this, 'shift'],
                'icon'     => 'fas fa-credit-card',

                'controllerRoute' => "/shop/admin-shop-check",
                'relation'        => ['shop_cashebox_id' => 'id'],
                'on gridInit'     => function ($e) {
                    /**
                     * @var $action BackendGridModelRelatedAction
                     */
                    $action = $e->sender;
                    $action->relatedIndexAction->backendShowings = false;
                    $action->relatedIndexAction->filters = false;
                    $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];

                    ArrayHelper::removeValue($visibleColumns, 'shop_cashebox_id');
                    $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                },
            ],


            'shifts' => [
                'class'    => BackendGridModelRelatedAction::class,
                'name'     => 'Смены',
                'priority' => 90,
                'callback' => [$this, 'shift'],
                'icon'     => 'fas fa-credit-card',

                'controllerRoute' => "/shop/admin-shop-cashebox-shift",
                'relation'        => ['shop_cashebox_id' => 'id'],
                'on gridInit'     => function ($e) {
                    /**
                     * @var $action BackendGridModelRelatedAction
                     */
                    $action = $e->sender;
                    $action->relatedIndexAction->backendShowings = false;
                    $action->relatedIndexAction->filters = false;
                    $visibleColumns = $action->relatedIndexAction->grid['visibleColumns'];

                    ArrayHelper::removeValue($visibleColumns, 'shop_cashebox_id');
                    $action->relatedIndexAction->grid['visibleColumns'] = $visibleColumns;

                },
            ],

            "create" => [
                'fields' => [$this, 'updateFields'],
            ],
            "update" => [
                'fields' => [$this, 'updateFields'],
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
                    'name',
                    'shop_store_id' => [
                        'class'        => WidgetField::class,
                        'widgetClass'  => AjaxSelectModel::class,
                        'widgetConfig' => [
                            'modelClass' => ShopStore::class,
                            'searchQuery' => function($word = '') {
                                $query = ShopStore::find()->isSupplier(false)->cmsSite();
                                if ($word) {
                                    $query->search($word);
                                }
                                return $query;
                            },
                        ],

                    ],

                    'is_active'   => [
                        'class'     => BoolField::class,
                        'allowNull' => false,
                    ],
                    'priority'   => [
                        'class'     => NumberField::class,
                    ],


                    'shop_cloudkassa_id' => [
                        'class'        => WidgetField::class,
                        'widgetClass'  => AjaxSelectModel::class,
                        'widgetConfig' => [
                            'modelClass' => ShopCloudkassa::class,
                            'searchQuery' => function($word = '') {
                                $query = ShopCloudkassa::find()->cmsSite();
                                if ($word) {
                                    $query->search($word);
                                }
                                return $query;
                            },
                        ],

                    ],

                ],
            ],
        ];
    }


}
