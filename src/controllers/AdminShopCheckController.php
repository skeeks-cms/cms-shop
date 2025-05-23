<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\controllers;

use Cassandra\DefaultColumn;
use skeeks\cms\backend\actions\BackendModelAction;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\grid\DefaultActionColumn;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\rbac\CmsManager;
use skeeks\cms\shop\models\ShopCachebox;
use skeeks\cms\shop\models\ShopCheck;
use yii\base\Event;
use yii\bootstrap\Alert;
use yii\helpers\ArrayHelper;
use yii\helpers\UnsetArrayValue;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminShopCheckController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = "Чеки";
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopCheck::class;

        $this->generateAccessActions = false;
        $this->permissionName = "shop/admin-shop-check";

        parent::init();
    }


    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            "view" => [
                'class' => BackendModelAction::class,
                'name' => 'Просмотр'
            ],

            'index' => [
                'on beforeRender' => function (Event $e) {
                    /*$e->content = Alert::widget([
                        'closeButton' => false,
                        'options'     => [
                            'class' => 'alert-default',
                        ],

                        'body' => <<<HTML
Для работы магазина можно добавить кассу
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

                        $query->cmsSite();
                        //$query->andWhere(['is_supplier' => 0]);
                    },
                    'defaultOrder'   => [
                        'created_at' => SORT_DESC,
                    ],
                    'visibleColumns' => [

                        /*'checkbox',*/
                        'actions',

                        'id',

                        'shop',
                        'shop_cashebox_id',
                        'shop_cashebox_shift_id',

                        'shop_order_id',
                        'doc_type',
                        'amount',

                        'cms_user_id',

                        'status',
                        'created_at',
                    ],
                    'columns'        => [

                        'created_at'             => [
                            'class' => DateTimeColumnData::class,
                        ],
                        'id'             => [
                            'value' => function(ShopCheck $model) {
                                return \yii\helpers\Html::a("{$model->asText}", "#", [
                                    'class' => "sx-trigger-action",
                                ]);
                            }
                        ],
                        'shop_cashebox_id'       => [
                            'format' => 'raw',
                            'value'  => function (ShopCheck $shopCheck) {

                                $result = [];

                                if ($shopCheck->shopCashebox) {
                                    return \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::widget([
                                        'controllerId'            => '/shop/admin-shop-cashebox',
                                        'modelId'                 => $shopCheck->shopCashebox->id,
                                        'content'                 => $shopCheck->shopCashebox->name,
                                        'isRunFirstActionOnClick' => true,
                                        'options'                 => [
                                            'class' => 'btn btn-xs btn-default',
                                            //'style' => 'cursor: pointer; border-bottom: 1px dashed;',
                                        ],
                                    ]);
                                }

                                return "";
                            },
                        ],

                        'shop'       => [
                            'format' => 'raw',
                            'label' => 'Магазин',
                            'value'  => function (ShopCheck $shopCheck) {

                                $result = [];

                                if ($shopCheck->shopCashebox && $shopCheck->shopCashebox->shopStore) {
                                    return \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::widget([
                                        'controllerId'            => '/shop/admin-shop-store',
                                        'modelId'                 => $shopCheck->shopCashebox->shopStore->id,
                                        'content'                 => $shopCheck->shopCashebox->shopStore->name,
                                        'isRunFirstActionOnClick' => true,
                                        'options'                 => [
                                            'class' => 'btn btn-xs btn-default',
                                            //'style' => 'cursor: pointer; border-bottom: 1px dashed;',
                                        ],
                                    ]);
                                }

                                return "";
                            },
                        ],

                        'shop_cashebox_shift_id' => [
                            'format' => 'raw',
                            'value'  => function (ShopCheck $shopCheck) {
                                $result = [];

                                if ($shopCheck->shopCasheboxShift) {
                                    return \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::widget([
                                        'controllerId'            => '/shop/admin-shop-cashebox-shift',
                                        'modelId'                 => $shopCheck->shopCasheboxShift->id,
                                        'content'                 => $shopCheck->shopCasheboxShift->asText,
                                        'isRunFirstActionOnClick' => true,
                                        'options'                 => [
                                            'class' => 'btn btn-xs btn-default',
                                            //'style' => 'cursor: pointer; border-bottom: 1px dashed;',
                                        ],
                                    ]);
                                }

                                return "";
                            },
                        ],


                        'shop_order_id' => [
                            'value'         => function(ShopCheck $shopCheck) {
                                if ($shopCheck->shopOrder) {
                                    return \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::widget([
                                        'controllerId'            => '/shop/admin-order',
                                        'modelId'                 => $shopCheck->shopOrder->id,
                                        'content'                 => $shopCheck->shopOrder->asText,
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

                        'cms_user_id' => [
                            'value'         => function(ShopCheck $shopCheck) {
                                if ($shopCheck->cmsUser) {
                                    return \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::widget([
                                        'controllerId'            => '/cms/admin-user',
                                        'modelId'                 => $shopCheck->cmsUser->id,
                                        'content'                 => $shopCheck->cmsUser->shortDisplayName,
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

                        'status'                 => [
                            'format' => 'raw',
                            'value'  => function (ShopCheck $shopCheck, $key) {


                                if ($shopCheck->status == ShopCheck::STATUS_APPROVED) {
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
                                } elseif ($shopCheck->status == ShopCheck::STATUS_WAIT) {
                                    $this->view->registerJs(<<<JS
$('tr[data-key={$key}]').addClass('sx-tr-orange');
JS
                                    );

                                    $this->view->registerCss(<<<CSS
tr.sx-tr-orange, tr.sx-tr-orange:nth-of-type(odd), tr.sx-tr-orange td
{
background: #fff3d5 !important;
}
CSS
                                    );
                                } elseif ($shopCheck->status == ShopCheck::STATUS_NEW) {
                                    $this->view->registerJs(<<<JS
$('tr[data-key={$key}]').addClass('sx-tr-new');
JS
                                    );

                                    $this->view->registerCss(<<<CSS
tr.sx-tr-new, tr.sx-tr-new:nth-of-type(odd), tr.sx-tr-new td
{
    opacity: 0.5;
}
tr.sx-tr-new:hover, tr.sx-tr-new:hover:nth-of-type(odd), tr.sx-tr-new:hover td
{
    opacity: 1;
}
CSS
                                    );
                                }


                                return $shopCheck->statusAsText;
                            },
                        ],
                        'doc_type'                 => [
                            'format' => 'raw',
                            'value'  => function (ShopCheck $shopCheck) {
                                return $shopCheck->docTypeAsText;
                            },
                        ],
                    ],
                ],
            ],


            "create"       => new UnsetArrayValue(),
            "update"       => new UnsetArrayValue(),
            "delete"       => new UnsetArrayValue(),
            "delete-multi" => new UnsetArrayValue(),
        ]);
    }


}
