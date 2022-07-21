<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\grid\DefaultActionColumn;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\rbac\CmsManager;
use skeeks\cms\shop\models\queries\ShopCasheboxShiftQuery;
use skeeks\cms\shop\models\ShopCachebox;
use skeeks\cms\shop\models\ShopCasheboxShift;
use skeeks\cms\shop\models\ShopStore;
use skeeks\cms\widgets\AjaxSelectModel;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\WidgetField;
use yii\base\Event;
use yii\bootstrap\Alert;
use yii\helpers\ArrayHelper;
use yii\helpers\UnsetArrayValue;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminShopCasheboxShiftController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = "Смены";
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopCasheboxShift::class;

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
                    $e->content = Alert::widget([
                        'closeButton' => false,
                        'options'     => [
                            'class' => 'alert-default',
                        ],

                        'body' => <<<HTML
Все смены
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

                        'checkbox',
                        'actions',

                        //'id',
                        'custom',
                        'shift_number',
                        'created_by',
                        'created_at',
                        'closed_at',
                    ],
                    'columns'        => [
                        'created_at' => [
                            'value'         => function(ShopCasheboxShift $model) {
                                return $model->created_at ? \Yii::$app->formatter->asDatetime($model->created_at) : "";
                            }
                        ],
                        'closed_at' => [
                            'value'         => function(ShopCasheboxShift $model) {
                                return $model->closed_at ? \Yii::$app->formatter->asDatetime($model->closed_at) : "";
                            }
                        ],
                        'custom' => [
                            'label'         => 'Касса',
                            'attribute'         => 'shop_cashebox_id',
                            'format'         => 'raw',
                            'value' => function(ShopCasheboxShift $casheboxShift) {
                                return $casheboxShift->shopCashebox->shopStore->name . " <br />" . $casheboxShift->shopCashebox->name;
                            },
                        ],
                        /*'is_active' => [
                            'class'      => BooleanColumn::class,
                            'trueValue'  => 1,
                            'falseValue' => 1,
                        ],

                        'name' => [
                            'class'         => DefaultActionColumn::class,
                            'viewAttribute' => 'asText',
                        ],*/

                    ],
                ],
            ],

            "create" => new UnsetArrayValue(),
            "update" => new UnsetArrayValue(),
        ]);
    }
}
