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
                'class' => BackendModelAction::class,
                'name' => 'Просмотр'
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
                        'created_at' => SORT_DESC,
                    ],
                    'visibleColumns' => [

                        'checkbox',
                        'actions',

                        'id',
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
                                return \yii\helpers\Html::a("Чек #{$model->id}", "#", [
                                    'class' => "sx-trigger-action",
                                ]);
                            }
                        ],
                        'shop_cashebox_id'       => [
                            'format' => 'raw',
                            'value'  => function (ShopCheck $shopCheck) {

                                $result = [];

                                if ($shopCheck->shopCashebox) {
                                    $result[] = $shopCheck->shopCashebox->name;
                                    if ($shopCheck->shopCashebox->shopCloudkassa) {
                                        $result[] = "<span style='color:gray;'>Работает через: ".$shopCheck->shopCashebox->shopCloudkassa->name."</span>";
                                    }
                                    if ($shopCheck->shopCashebox->shopStore) {
                                        $result[] = "<span style='color:gray;'>".$shopCheck->shopCashebox->shopStore->name."</span>";
                                    }
                                }

                                return implode("<br>", $result);
                            },
                        ],
                        'shop_cashebox_shift_id' => [
                            'format' => 'raw',
                            'value'  => function (ShopCheck $shopCheck) {
                                $result = [];

                                if ($shopCheck->shopCasheboxShift) {
                                    $result[] = "Смена #".$shopCheck->shopCasheboxShift->shift_number;

                                    if ($shopCheck->shopCasheboxShift->createdBy) {
                                        $result[] = "<span style='color:gray;'>Кассир: ".$shopCheck->shopCasheboxShift->createdBy->shortDisplayName."</span>";
                                    }
                                }

                                return implode("<br>", $result);
                            },
                        ],
                        'shop_order_id'          => [
                            'format' => 'raw',
                            'value'  => function (ShopCheck $shopCheck) {
                                $result = [];

                                $result[] = $shopCheck->shopOrder->asText;

                                return implode("<br>", $result);
                            },
                        ],
                        'status'                 => [
                            'format' => 'raw',
                            'value'  => function (ShopCheck $shopCheck) {

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
