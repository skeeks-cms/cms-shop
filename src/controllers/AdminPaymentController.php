<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use kartik\datecontrol\DateControl;
use skeeks\cms\backend\actions\BackendModelAction;
use skeeks\cms\backend\actions\BackendModelLogAction;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\widgets\AjaxControllerActionsWidget;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\grid\UserColumnData;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsCompany;
use skeeks\cms\models\CmsContractor;
use skeeks\cms\models\CmsContractorBank;
use skeeks\cms\models\CmsDeal;
use skeeks\cms\models\CmsUser;
use skeeks\cms\money\Money;
use skeeks\cms\queryfilters\QueryFiltersEvent;
use skeeks\cms\shop\models\queries\ShopPaymentQuery;
use skeeks\cms\shop\models\ShopBill;
use skeeks\cms\shop\models\ShopCashebox;
use skeeks\cms\shop\models\ShopCheck;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\shop\models\ShopOrderChange;
use skeeks\cms\shop\models\ShopPayment;
use skeeks\cms\shop\models\ShopPaySystem;
use skeeks\cms\shop\models\ShopStore;
use skeeks\cms\widgets\AjaxSelectModel;
use skeeks\cms\widgets\formInputs\daterange\DaterangeInputWidget;
use skeeks\cms\widgets\GridView;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextareaField;
use skeeks\yii2\form\fields\WidgetField;
use yii\base\Event;
use yii\base\WidgetEvent;
use yii\bootstrap\Alert;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminPaymentController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Платежи по заказам');
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopPayment::class;

        $this->permissionName = "shop/admin-order";

        $this->generateAccessActions = false;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $result = ArrayHelper::merge(parent::actions(), [

            "view" => [
                'class'    => BackendModelAction::class,
                'priority' => 80,
                'name'     => 'Просмотр',
                'icon'     => 'fas fa-info-circle',
            ],

            "index" => [
                "filters" => [
                    "visibleFilters" => [
                        //'id',
                        'q',
                        'shop_order_id',
                        'shop_pay_system_id',
                        'additional',
                        'is_debit',
                        'date',
                    ],
                    'filtersModel' => [

                        'rules'            => [
                            ['q', 'safe'],
                            ['additional', 'safe'],
                            ['is_debit', 'safe'],
                            ['date', 'safe'],
                        ],
                        'attributeDefines' => [
                            'q',
                            'additional',
                            'is_debit',
                            'date',
                        ],

                        'fields' => [
                            'q' => [
                                'label'          => 'Поиск',
                                'elementOptions' => [
                                    'placeholder' => 'Поиск',
                                ],
                                'on apply'       => function (QueryFiltersEvent $e) {
                                    /**
                                     * @var $query ShopPaymentQuery
                                     */
                                    $query = $e->dataProvider->query;

                                    if ($e->field->value) {
                                        $query->search($e->field->value);
                                        $query->joinWith('company as company');
                                        $query->joinWith('senderContractor as senderContractor');
                                        $query->orWhere([
                                            'LIKE', 'company.name', $e->field->value
                                        ]);
                                        $query->orWhere([
                                            'LIKE', 'senderContractor.name', $e->field->value
                                        ]);
                                    }
                                },
                            ],
                            'date' => [
                                'class' => WidgetField::class,
                                'widgetClass'  => DaterangeInputWidget::class,
                                'widgetConfig' => [
                                    'options' => [
                                        'placeholder' => 'Диапазон дат'
                                    ],
                                ],
                                'label'          => 'Дата',
                                /*'elementOptions' => [
                                    'placeholder' => 'Поиск',
                                ],*/
                                'on apply'       => function (QueryFiltersEvent $e) {
                                    /**
                                     * @var $query ShopPaymentQuery
                                     */
                                    $query = $e->dataProvider->query;

                                    if ($e->field->value) {
                                        $data = explode("-", $e->field->value);
                                        $start = strtotime(trim(ArrayHelper::getValue($data, 0) . " 00:00:00"));
                                        $end = strtotime(trim(ArrayHelper::getValue($data, 1) .  " 23:59:59"));
                                        
                                        $query->andWhere(['>=', "created_at", $start]);
                                        $query->andWhere(['<=', "created_at", $end]);
                                        
                                        
                                    }
                                },
                            ],
                            'additional' => [
                                'label'    => 'Дополнительно',
                                'class'    => SelectField::class,
                                'items'    => [
                                    'need_to_bill' => 'Необходимо разобрать'
                                ],
                                'on apply' => function (QueryFiltersEvent $e) {
                                    /**
                                     * @var $query ActiveQuery
                                     */
                                    $query = $e->dataProvider->query;

                                    if ($e->field->value) {
                                        if ($e->field->value == 'need_to_bill') {
                                            
                                            $query->joinWith("bills as bills");
                                            $query->joinWith("deals as deals");
                                            /*$query->joinWith("senderContractor as senderContractor");
                                            $query->joinWith("senderContractor.senderBills as senderBills");*/

                                            //В платеже ничего не указано
                                            $query->andWhere([
                                                "bills.id" => null,
                                            ]);
                                            $query->andWhere([
                                                "deals.id" => null,
                                            ]);
                                            /*$query->andWhere([
                                                "senderBills.paid_at" => null,
                                            ]);*/

                                            $ourContractors = CmsContractor::find()->our()->select(["id"]);

                                            //Получатель - наш контрагент
                                            $query->andWhere([
                                                ShopPayment::tableName().".receiver_contractor_id" => $ourContractors,
                                            ]);

                                            /*$query->andWhere([
                                                "not in",
                                                ShopPayment::tableName().".sender_contractor_id",
                                                $ourContractors,
                                            ]);*/
                                        }



                                        $query->groupBy(ShopPayment::tableName().".id");

                                    }
                                },
                            ],
                            'is_debit' => [
                                'label'    => 'Тип платежа',
                                'class'    => SelectField::class,
                                'items'    => [
                                    'yes' => 'Приход',
                                    'no' => 'Расход'
                                ],
                                'on apply' => function (QueryFiltersEvent $e) {
                                    /**
                                     * @var $query ActiveQuery
                                     */
                                    $query = $e->dataProvider->query;

                                    if ($e->field->value == 'yes') {
                                        $query->andWhere([
                                            "is_debit" => 1,
                                        ]);
                                    } elseif ($e->field->value == 'no') {
                                        $query->andWhere([
                                            "is_debit" => 0,
                                        ]);
                                    }
                                },
                            ]
                        ]
                    ]
                ],

                'grid' => [
                    'defaultOrder' => [
                        'created_at' => SORT_DESC,
                    ],

                    'on init' => function (Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ShopPaymentQuery
                         */
                        $query = $e->sender->dataProvider->query;

                        $query->forManager()->cmsSite();
                    },

                    'visibleColumns' => [
                        //'checkbox',
                        'actions',

                        /*'created_at',*/

                        'custom',

                        'amount',
                        
                        
                        'info',
                        
                        /*'shop_pay_system_id',*/

                        /*'shop_order_id',*/

                        /*'cms_company_id',
                        'cms_user_id',*/
                        'client',

                        /*'shop_check_id',*/

                        /*'comment',*/

                    ],
                    'columns'        => [

                        'custom'             => [
                            'attribute' => 'created_at',
                            'format' => 'raw',
                            'label' => 'Платеж',
                            /*'headerOptions'  => [
                                'style' => 'min-width: 200px;',
                            ],*/
                            'value' => function(ShopPayment $model) {
                                return \yii\helpers\Html::a( ($model->is_debit ? "Поступление" : "Оплата") . "&nbsp;№{$model->id}", "#", [
                                    'class' => "sx-trigger-action",
                                    'style' => "font-size: 15px;",
                                ]).
                                    "<div title='" . \Yii::$app->formatter->asDatetime($model->created_at) . "'>" . \Yii::$app->formatter->asDate($model->created_at) . "</div>";
                            }
                        ],

                        'created_at' => [
                            'class' => DateTimeColumnData::class,
                            'view_type' => DateTimeColumnData::VIEW_DATE,
                        ],
                        'cms_user_id' => [
                            'class' => UserColumnData::class,
                        ],
                        'client' => [
                            'format' => 'raw',
                            'label' => 'Клиент',
                            
                            'value' => function (ShopPayment $crmDeal) {

                                $result = [];
                                if ($crmDeal->cms_company_id) {
                                    $result[] = AjaxControllerActionsWidget::widget([
                                        'controllerId' => '/cms/admin-cms-company',
                                        'modelId'      => $crmDeal->company->id,
                                        'content'      => '<i class="fas fa-users"></i> '.$crmDeal->company->asText,
                                        'options'      => [
                                            'style' => 'text-align: left;',
                                        ],
                                    ]);
                                }
                                
                                if ($crmDeal->cms_user_id) {
                                    $result[] = AjaxControllerActionsWidget::widget([
                                        'controllerId' => '/cms/admin-user',
                                        'modelId'      => $crmDeal->cmsUser->id,
                                        'content'      => '<i class="fas fa-users"></i> '.$crmDeal->cmsUser->asText,
                                        'options'      => [
                                            'style' => 'text-align: left;',
                                        ],
                                    ]);
                                }
                                return implode(", ", $result);


                            },
                        ],
                        'cms_company_id' => [
                            'value' => function (ShopPayment $crmDeal) {

                                if ($crmDeal->cms_company_id) {
                                    return AjaxControllerActionsWidget::widget([
                                        'controllerId' => '/cms/admin-cms-company',
                                        'modelId'      => $crmDeal->company->id,
                                        'content'      => '<i class="fas fa-users"></i> '.$crmDeal->company->asText,
                                        'options'      => [
                                            'style' => 'text-align: left;',
                                        ],
                                    ]);
                                }
                                return '';


                            },
                        ],
                        'amount' => [
                            'value' => function(ShopPayment $shopPayment) {
                                if ($shopPayment->is_debit) {
                                    return "<span style='color: green;'>+{$shopPayment->money}</span>";
                                } else {
                                    return "<span style='color: red;'>-{$shopPayment->money}</span>";
                                }
                            }
                        ],
                        'comment' => [
                            'value' => function(ShopPayment $shopPayment) {
                                return "<span style='color: gray;'>{$shopPayment->comment}</span>";
                            }
                        ],

                        'shop_order_id' => [
                            'value'         => function(ShopPayment $shopPayment) {
                                if ($shopPayment->shopOrder) {
                                    return \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::widget([
                                        'controllerId'            => '/shop/admin-order',
                                        'modelId'                 => $shopPayment->shopOrder->id,
                                        'content'                 => $shopPayment->shopOrder->asText,
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

                        'shop_check_id' => [
                            'value'         => function(ShopPayment $shopPayment) {
                                if ($shopPayment->shopCheck) {
                                    return \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::widget([
                                        'controllerId'            => '/shop/admin-shop-check',
                                        'modelId'                 => $shopPayment->shopCheck->id,
                                        'content'                 => $shopPayment->shopCheck->asText,
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

                        'info' => [
                            'format' => 'raw',
                            'label' => 'Информация',
                            
                            'value' => function(ShopPayment $shopPayment) {
                                $data = [];
                                if ($shopPayment->shop_store_id) {
                                    $data[] = 'Оплата в магазине (' . $shopPayment->shopStore->name . ')';
                                    $data[] = "<div style='color: gray;'>{$shopPayment->shopStorePaymentTypeAsText}</div>";
                                    if ($shopPayment->shopCashebox) {
                                        $data[] = "<div style='color: gray;'>Касса: {$shopPayment->shopCashebox->name}</div>";
                                    }
                                    if ($shopPayment->shopCasheboxShift) {
                                        $data[] = "<div style='color: gray;'>{$shopPayment->shopCasheboxShift->asText}</div>";
                                    }


                                } else {
                                    if ($shopPayment->shopPaySystem) {
                                        $data[] = "<div style='color: gray;'>{$shopPayment->shopPaySystem->name}</div>";
                                    }
                                    if ($shopPayment->senderContractor && $shopPayment->receiverContractor) {
                                        $data[] = "<div>{$shopPayment->senderContractor->asText} → {$shopPayment->receiverContractor->asText}</div>";
                                    }
                                }
                                if($shopPayment->comment) {
                                    $data[] = "<div style='padding: 10px; font-size: 14px; background: #f9f9f9;'>{$shopPayment->comment}</div>";
                                }

                                if ($shopPayment->deals) {
                                    $data[] = '<hr style="border-color: #f3f3f3;"/>';
                                    foreach ($shopPayment->deals as $crmDeal) {
                                        $data[] = "<div>".AjaxControllerActionsWidget::widget([
                                                'controllerId' => '/cms/admin-cms-deal',
                                                'modelId'      => $crmDeal->id,
                                                'content'      => '<i class="far fa-file"></i> '.$crmDeal->asText,
                                                'options'      => [
                                                    'style' => 'text-align: left;',
                                                ],
                                            ])."</div>";
                                    }
                                }

                                if ($shopPayment->bills) {
                                    $data[] = '<hr style="border-color: #f3f3f3;"/>';
                                    foreach ($shopPayment->bills as $crmBill) {
                                        $data[] = "<div>".AjaxControllerActionsWidget::widget([
                                                'controllerId' => '/cms/admin-cms-bill',
                                                'modelId'      => $crmBill->id,
                                                'content'      => '<i class="far fa-file"></i> '.$crmBill->asText,
                                                'options'      => [
                                                    'style' => 'text-align: left;',
                                                ],
                                            ])."</div>";
                                    }
                                }

                                if ($shopPayment->shop_order_id) {
                                    $data[] = '<hr style="border-color: #f3f3f3;"/>';
                                        $data[] = "<div>".AjaxControllerActionsWidget::widget([
                                                'controllerId' => '/shop/admin-order',
                                                'modelId'      => $shopPayment->shop_order_id,
                                                'content'      => '<i class="far fa-file"></i> '.$shopPayment->shopOrder->asText,
                                                'options'      => [
                                                    'style' => 'text-align: left;',
                                                ],
                                            ])."</div>";
                                }
                                
                                if ($shopPayment->shop_check_id) {
                                    $data[] = '<hr style="border-color: #f3f3f3;"/>';
                                        $data[] = "<div>".AjaxControllerActionsWidget::widget([
                                                'controllerId' => '/shop/admin-shop-check',
                                                'modelId'      => $shopPayment->shop_check_id,
                                                'content'      => '<i class="far fa-file"></i> '.$shopPayment->shopCheck->asText,
                                                'options'      => [
                                                    'style' => 'text-align: left;',
                                                ],
                                            ])."</div>";
                                }

                                return implode("", $data);
                            }
                        ],
                    ],


                    'on afterRun' => function (WidgetEvent $event) {

                        /**
                         * @var $grid GridView
                         * @var $query ActiveQuery
                         */
                        $grid = $event->sender;
                        $query = clone $grid->dataProvider->query;
                        $queryCreadit = clone $grid->dataProvider->query;

                        $tableName = ShopPayment::tableName();
                        $result = $query
                            ->select([$tableName.".id", 'sum' => new Expression("SUM({$tableName}.amount)")])
                            ->andWhere(['is_debit' => 1])
                            ->asArray()->one();
                        $sumAmount = ArrayHelper::getValue($result, 'sum');
                        $money = new Money($sumAmount, 'RUB');

                        /**
                         * @var ShopPaySystem $shopPaySystem
                         */
                        $paysystemMoney = '';
                        if ($paysestems = ShopPaySystem::find()->sort()->all()) {
                            foreach ($paysestems as $shopPaySystem)
                            {
                                $queryTmp = clone $grid->dataProvider->query;

                                $tableName = ShopPayment::tableName();
                                $result = $queryTmp
                                    ->select([$tableName.".id", 'sum' => new Expression("SUM({$tableName}.amount)")])
                                    ->andWhere([$tableName.'.is_debit' => 1])
                                    ->andWhere([$tableName.'.shop_pay_system_id' => $shopPaySystem->id])
                                    ->asArray()->one();
                                $sumAmountTmp = ArrayHelper::getValue($result, 'sum');
                                $moneyTmp = new Money($sumAmountTmp, 'RUB');

                                $paysystemMoney .= "<li>{$shopPaySystem->name}: {$moneyTmp}</li>";
                            }
                        }


                        $resultCreadit = $queryCreadit
                            ->select([$tableName.".id", 'sum' => new Expression("SUM({$tableName}.amount)")])
                            ->andWhere(['is_debit' => 0])
                            ->asArray()
                            ->one();

                        $sumAmountCredit = ArrayHelper::getValue($resultCreadit, 'sum');
                        $moneyCreadit = new Money($sumAmountCredit, 'RUB');

                        $event->result = Alert::widget([
                            'options'     => [
                                'class' => 'alert alert-default',
                            ],
                            'closeButton' => false,
                            'body'        => <<<HTML
<div class="g-font-weight-300">
<span class="g-font-size-40">Приход: <span title="" style="">{$money}</span></span><br>
<ul>{$paysystemMoney}</ul>
<span class="g-font-size-40">Расход: <span title="" style="">{$moneyCreadit}</span></span>
</div>
HTML
                            ,
                        ]);
                    },
                ],
            ],

            'create' => [
                'fields'        => [$this, 'updateFields'],
                'buttons'         => ["save"],
            ],
            'update' => [
                'fields'        => [$this, 'updateFields'],
                'buttons'         => ["save"],
            ],
            'delete' => [
                'accessCallback' => function (BackendModelAction $action) {

                    /**
                     * @var $model ShopPayment
                     */
                    $model = $action->model;

                    if (!$model) {
                        return false;
                    }

                    if ($model->bills || $model->deals || $model->external_id) {
                        return false;
                    }

                    return true;
                },
            ],
            "log" => [
                'class'    => BackendModelLogAction::class,
            ],
        ]);

       /// ArrayHelper::remove($result, "create");
        /*ArrayHelper::remove($result, "delete");*/
        ArrayHelper::remove($result, "delete-multi");

        return $result;
    }

    public function updateFields($action)
    {
        /**
         * @var $model ShopPayment
         */
        $model = $action->model;
        $model->load(\Yii::$app->request->get());

        $result = [];

        $result['main'] = [
            'class'  => FieldSet::class,
            'name'   => \Yii::t('skeeks/shop/app', 'Main'),
            'fields' => [

                'is_debit' => [
                    'class'       => SelectField::class,
                    'label' => 'Тип операции',
                    'items' => [
                        '1' => 'Нам поступили деньги/Внесли деньги в кассу',
                        '0' => 'Мы заплатили/выплатили/выдали из кассы',
                    ],
                    'elementOptions'       => [
                        'data' => [
                            'form-reload' => 'true',
                        ],
                    ],
                ],

                'shop_pay_system_id' => [
                    'class'        => WidgetField::class,
                    /*'label' => 'Способ оплаты',*/
                    'widgetClass'  => AjaxSelectModel::class,
                    'widgetConfig' => [
                        'modelClass' => ShopPaySystem::class,
                        'searchQuery' => function($word = '') {
                            $query = ShopPaySystem::find()->cmsSite();
                            if ($word) {
                                $query->search($word);
                            }
                            return $query;
                        },
                    ],
                ],

                'amount' => [
                    'class'        => NumberField::class,
                ],

                'comment' => [
                    'class'        => TextareaField::class,
                ],
            ],
        ];

        $result['client'] = [
            'class'  => FieldSet::class,
            'name'   => 'Компания или клиент (заполнить хотя бы одно)',
            'fields' => [

                'cms_company_id' => [
                    'class'        => WidgetField::class,
                    'widgetClass'  => AjaxSelectModel::class,
                    'widgetConfig' => [
                        'options'       => [
                            'data' => [
                                'form-reload' => 'true',
                            ],
                        ],
                        'modelClass' => CmsCompany::class,
                        'searchQuery' => function($word = '') {
                            $query = CmsCompany::find()->forManager();
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
                        'options'       => [
                            'data' => [
                                'form-reload' => 'true',
                            ],
                        ],
                        'modelClass' => CmsUser::class,
                        'searchQuery' => function($word = '') {
                            $query = CmsUser::find()->forManager();
                            if ($word) {
                                $query->search($word);
                            }
                            return $query;
                        },
                    ],
                ],
            ],
        ];


        $dealData = [];

        if ($model->cms_company_id || $model->cms_user_id) {
            $query = CmsDeal::find()
                ->forManager()
            ;

            if ($model->cms_company_id) {
                $query->andWhere(['cms_company_id' => $model->cms_company_id]);
            }

            if ($model->cms_user_id) {
                $query->andWhere(['cms_user_id' => $model->cms_user_id]);
            }

            $dealData = ArrayHelper::map($query->all(), 'id', 'asText');
        }

        $billData = [];

        if ($model->cms_company_id || $model->cms_user_id) {
            $query = ShopBill::find()
                ->forManager()
            ;

            if ($model->cms_company_id) {
                $query->andWhere(['cms_company_id' => $model->cms_company_id]);
            }

            if ($model->cms_user_id) {
                $query->andWhere(['cms_user_id' => $model->cms_user_id]);
            }


            if ($model->isNewRecord) {
                $query->andWhere(['paid_at' => null]);
            } else {
                //$billIds = ArrayHelper::getValue($model->bills, "id", "id");
                /*$query->andWhere([
                    'or',
                    ['paid_at' => null],
                    ['id' => $billIds]
                ]);*/
            }

            $billData = ArrayHelper::map($query->all(), 'id', 'asFullText');
            
            if ($model->bills && !$model->isNewRecord) {
                $billData = ArrayHelper::merge(ArrayHelper::map($model->bills, "id", "asFullText"), (array) $billData);
            }
        }



        $result['relations'] = [
            'class'  => FieldSet::class,
            'name'   => "Дополнительны связи",
            'fields' => [

                'deals' => [
                    'class'        => SelectField::class,
                    'multiple' => true,
                    'items'  => $dealData,
                ],
                'bills' => [
                    'class'        => SelectField::class,
                    'multiple' => true,
                    'items'  => $billData,
                ],

                'shop_order_id' => [
                    'class'        => WidgetField::class,
                    'label' => 'Заказ/Продажа/Возврат',
                    'widgetClass'  => AjaxSelectModel::class,
                    'widgetConfig' => [
                        'modelClass' => ShopOrder::class,
                        'searchQuery' => function($word = '') {
                            $query = ShopOrder::find()->cmsSite();
                            if ($word) {
                                $query->search($word);
                            }
                            return $query;
                        },
                    ],
                ],

                'shop_check_id' => [
                    'class'        => WidgetField::class,
                    'label' => 'Чек',
                    'widgetClass'  => AjaxSelectModel::class,
                    'widgetConfig' => [
                        'modelClass' => ShopCheck::class,
                        'searchQuery' => function($word = '') {
                            $query = ShopCheck::find()->cmsSite();
                            if ($word) {
                                $query->search($word);
                            }
                            return $query;
                        },
                    ],
                ],


            ],
        ];


        $senderData = [];

        if ($model->cms_company_id || $model->cms_user_id) {

            $query = CmsContractor::find()
                ->forManager()
            ;

            if ($model->cms_company_id) {
                $query->joinWith('companies as companies');
                $query->andWhere(['companies.id' => $model->cms_company_id]);
            }

            if ($model->cms_user_id) {
                $query->joinWith('users as users');
                $query->andWhere(['users.id' => $model->cms_user_id]);
            }

            $senderData = ArrayHelper::map($query->all(), 'id', 'asText');
        }

        $receiverData = ArrayHelper::map(CmsContractor::find()->our()->all(), 'id', 'asText');
        
        

        if ($model->is_debit == 0) {
            $receiverDataTmp = $receiverData;

            $receiverData = $senderData;
            $senderData = $receiverDataTmp;
        }

        $senderBankData = [];

        if ($model->sender_contractor_id) {
            $senderBankData = CmsContractorBank::find()->andWhere(['cms_contractor_id' => $model->sender_contractor_id])->all();
            $senderBankData = ArrayHelper::map($senderBankData, "id", "asText");
        }

        $receiverBankData = [];
        if ($model->receiver_contractor_id) {
            $receiverBankData = CmsContractorBank::find()->andWhere(['cms_contractor_id' => $model->receiver_contractor_id])->all();
            $receiverBankData = ArrayHelper::map($receiverBankData, "id", "asText");
        }
        


        if ($model->receiver_contractor_id && !$model->isNewRecord) {
        $receiverData = ArrayHelper::merge((array) $receiverData, [
            $model->receiverContractor->id => $model->receiverContractor->asText
        ]);
    }
    


        $result['legal'] = [
            'class'  => FieldSet::class,
            'name'   => 'Реквизиты',
            'fields' => [

                'sender_contractor_id' => [
                    'class'        => SelectField::class,
                    'items'  => $senderData,
                    'elementOptions'       => [
                        'data' => [
                            'form-reload' => 'true',
                        ],
                    ],
                ],

                'sender_contractor_bank_id' => [
                    'class'        => SelectField::class,
                    'items'  => $senderBankData,
                ],

                'receiver_contractor_id' => [
                    'class'        => SelectField::class,
                    'items'  => $receiverData,
                    'elementOptions'       => [
                        'data' => [
                            'form-reload' => 'true',
                        ],
                    ],
                ],

                'receiver_contractor_bank_id' => [
                    'class'        => SelectField::class,
                    'items'  => $receiverBankData,
                ],

            ],
        ];

        //Если платеж поступил из внешней системы
        if (!$model->isNewRecord && $model->external_id) {
            $result['legal']['fields']['sender_contractor_id']['elementOptions']['disabled'] = 'disabled';
            $result['legal']['fields']['sender_contractor_bank_id']['elementOptions']['disabled'] = 'disabled';
            $result['legal']['fields']['receiver_contractor_id']['elementOptions']['disabled'] = 'disabled';
            $result['legal']['fields']['receiver_contractor_bank_id']['elementOptions']['disabled'] = 'disabled';

            $result['main']['fields']['amount']['elementOptions']['disabled'] = 'disabled';
            $result['main']['fields']['comment']['elementOptions']['disabled'] = 'disabled';
            $result['main']['fields']['is_debit']['elementOptions']['disabled'] = 'disabled';
            $result['main']['fields']['shop_pay_system_id']['widgetConfig']['options']['disabled'] = 'disabled';
        }

        $result['shop'] = [
            'class'  => FieldSet::class,
            'name'   => "Связь с магазном",
            'fields' => [


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

                'shop_store_payment_type' => [
                    'class'       => SelectField::class,
                    'label' => 'Тип оплаты в магазине',
                    'items' => ShopPayment::getShopStorePaymentTypes(),
                ],

            ],
        ];
        
        //Если платеж поступил из внешней системы
        if (!$model->isNewRecord && !$model->external_id) {

            $result['additional'] = [
            'class'  => FieldSet::class,
            'name'   => "Дополнительно",
            'fields' => [


                'created_at' => [
                    'class'        => WidgetField::class,
                    'widgetClass'  => DateControl::class,
                    /*'widgetConfig' => [
                        'modelClass' => ShopStore::class,
                        'searchQuery' => function($word = '') {
                            $query = ShopStore::find()->isSupplier(false)->cmsSite();
                            if ($word) {
                                $query->search($word);
                            }
                            return $query;
                        },
                    ],*/
                ],


            ],
        ];
        }
        

        return $result;
    }

}
