<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\actions\BackendModelAction;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\widgets\BackendEntityLink;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\shop\models\ShopCheck;
use yii\base\Event;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
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
                            'format' => 'raw',
                            'value' => function(ShopCheck $model) {
                                return $this->renderEntityLink(
                                    '/shop/admin-shop-check',
                                    $model->id,
                                    $model->asText
                                );
                            },
                        ],
                        'shop_cashebox_id'       => [
                            'format' => 'raw',
                            'value'  => function (ShopCheck $shopCheck) {

                                if ($shopCheck->shopCashebox) {
                                    return $this->renderEntityLink(
                                        '/shop/admin-shop-cashebox',
                                        $shopCheck->shopCashebox->id,
                                        $shopCheck->shopCashebox->name
                                    );
                                }

                                return "";
                            },
                        ],

                        'shop'       => [
                            'format' => 'raw',
                            'label' => 'Магазин',
                            'value'  => function (ShopCheck $shopCheck) {

                                if ($shopCheck->shopCashebox && $shopCheck->shopCashebox->shopStore) {
                                    return $this->renderEntityLink(
                                        '/shop/admin-shop-store',
                                        $shopCheck->shopCashebox->shopStore->id,
                                        $shopCheck->shopCashebox->shopStore->name
                                    );
                                }

                                return "";
                            },
                        ],

                        'shop_cashebox_shift_id' => [
                            'format' => 'raw',
                            'value'  => function (ShopCheck $shopCheck) {
                                if ($shopCheck->shopCasheboxShift) {
                                    return $this->renderEntityLink(
                                        '/shop/admin-shop-cashebox-shift',
                                        $shopCheck->shopCasheboxShift->id,
                                        $shopCheck->shopCasheboxShift->asText
                                    );
                                }

                                return "";
                            },
                        ],


                        'shop_order_id' => [
                            'format'        => 'raw',
                            'value'         => function(ShopCheck $shopCheck) {
                                if ($shopCheck->shopOrder) {
                                    return $this->renderEntityLink(
                                        '/shop/admin-order',
                                        $shopCheck->shopOrder->id,
                                        $shopCheck->shopOrder->asText
                                    );
                                } else {
                                    return '';
                                }
                            },
                        ],

                        'cms_user_id' => [
                            'format'        => 'raw',
                            'value'         => function(ShopCheck $shopCheck) {
                                if ($shopCheck->cmsUser) {
                                    return $this->renderEntityLink(
                                        '/cms/admin-user',
                                        $shopCheck->cmsUser->id,
                                        $shopCheck->cmsUser->shortDisplayName
                                    );
                                } else {
                                    return '';
                                }
                            },
                        ],

                        'status'                 => [
                            'format' => 'raw',
                            'value'  => function (ShopCheck $shopCheck) {
                                return self::renderStatus($shopCheck);
                            },
                        ],
                        'doc_type'                 => [
                            'format' => 'raw',
                            'value'  => function (ShopCheck $shopCheck) {
                                return Html::tag('span', Html::encode($shopCheck->docTypeAsText), [
                                    'class' => 'sx-collection-cell__secondary',
                                ]);
                            },
                        ],
                        'amount' => [
                            'format' => 'raw',
                            'value'  => function (ShopCheck $shopCheck) {
                                return Html::tag('span', Html::encode((string)$shopCheck->amount), [
                                    'class' => 'sx-collection-cell__amount',
                                ]);
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

    private function renderEntityLink($controllerId, $modelId, $label)
    {
        return BackendEntityLink::widget([
            'controllerId' => $controllerId,
            'modelId'      => $modelId,
            'label'        => (string)$label,
            'options'      => [
                'class' => 'sx-collection-cell__primary',
            ],
        ]);
    }

    private static function renderStatus(ShopCheck $shopCheck)
    {
        $statusClass = '';
        if ($shopCheck->status === ShopCheck::STATUS_APPROVED) {
            $statusClass = 'sx-status--success';
        } elseif ($shopCheck->status === ShopCheck::STATUS_WAIT) {
            $statusClass = 'sx-status--warning';
        } elseif ($shopCheck->status === ShopCheck::STATUS_ERROR) {
            $statusClass = 'sx-status--danger';
        }

        return Html::tag('span', Html::encode($shopCheck->getStatusAsText()), [
            'class' => trim('sx-status '.$statusClass),
        ]);
    }


}
