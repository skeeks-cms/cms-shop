<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\actions\BackendGridModelAction;
use skeeks\cms\backend\actions\BackendModelAction;
use skeeks\cms\backend\BackendController;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\grid\DefaultActionColumn;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\grid\UserColumnData;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsUser;
use skeeks\cms\rbac\CmsManager;
use skeeks\cms\shop\models\queries\ShopCasheboxShiftQuery;
use skeeks\cms\shop\models\ShopCachebox;
use skeeks\cms\shop\models\ShopCashebox;
use skeeks\cms\shop\models\ShopCashebox2user;
use skeeks\cms\shop\models\ShopCasheboxShift;
use skeeks\cms\shop\models\ShopStore;
use skeeks\cms\widgets\AjaxSelectModel;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\TextareaField;
use skeeks\yii2\form\fields\WidgetField;
use yii\base\Event;
use yii\bootstrap\Alert;
use yii\helpers\ArrayHelper;
use yii\helpers\UnsetArrayValue;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminShopCashebox2userController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = "Кассиры";
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopCashebox2user::class;

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


            'index' => [
                'on beforeRender' => function (Event $e) {
                    /*$e->content = Alert::widget([
                        'closeButton' => false,
                        'options'     => [
                            'class' => 'alert-default',
                        ],

                        'body' => <<<HTML
Все смены
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
                         * @var $query ShopCasheboxShiftQuery
                         */
                        $query = $e->sender->dataProvider->query;

                        $query
                            ->innerJoinWith('shopCashebox as shopCashebox')
                            ->innerJoinWith('shopCashebox.shopStore as shopStore')
                            ->andWhere(['shopStore.cms_site_id' => \Yii::$app->skeeks->site->id]);
                        //$query->andWhere(['is_supplier' => 0]);
                    },
                    'defaultOrder'   => [
                        'id' => SORT_DESC,
                    ],
                    'visibleColumns' => [

                        /*'checkbox',*/
                        'actions',

                        'cms_user_id',
                        'cashiers_name',
                        'comment',
                        'shop_cashebox_id',
                    ],
                    'columns'        => [

                        /*'cms_user_id' => [
                            'class'         => UserColumnData::class
                        ],*/

                        /*'is_active' => [
                            'class'      => BooleanColumn::class,
                            'trueValue'  => 1,
                            'falseValue' => 1,
                        ],

                        'name' => [
                            'class'         => DefaultActionColumn::class,
                            'viewAttribute' => 'asText',
                        ],*/

                        'cashiers_name' => [
                            'value' => function(ShopCashebox2user $model) {
                                return $model->cashiers_name ? $model->cashiers_name : $model->cmsUser->shortDisplayName;
                            }
                        ]

                    ],
                ],
            ],

            "delete-multi" => new UnsetArrayValue(),
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
                    'shop_cashebox_id' => [
                        'class'        => WidgetField::class,
                        'widgetClass'  => AjaxSelectModel::class,
                        'widgetConfig' => [
                            'modelClass' => ShopCashebox::class,
                            'searchQuery' => function($word = '') {
                                $query = ShopCashebox::find()->cmsSite();
                                if ($word) {
                                    $query->search($word);
                                }
                                return $query;
                            },
                        ],
                    ],


                    'cms_user_id' => [
                        'class'        => WidgetField::class,
                        'widgetClass'  => AjaxSelectModel::class,
                        'widgetConfig' => [
                            'modelClass' => CmsUser::class,
                            'searchQuery' => function($word = '') {
                                $query = CmsUser::findByAuthAssignments(CmsManager::ROLE_WORKER)->cmsSite();
                                if ($word) {
                                    $query->search($word);
                                }
                                return $query;
                            },
                        ],

                    ],


                    'cashiers_name',
                    'comment' => [
                        'class' => TextareaField::class
                    ],
                ],
            ],
        ];
    }

}
