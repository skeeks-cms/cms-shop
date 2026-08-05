<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use chillerlan\QRCode\Data\Number;
use skeeks\cms\backend\BackendAction;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\widgets\BackendEntityLink;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\grid\UserColumnData;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsUser;
use skeeks\cms\shop\models\ShopBonusTransaction;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\shop\models\ShopOrderChange;
use skeeks\cms\shop\models\ShopPayment;
use skeeks\cms\widgets\AjaxSelectModel;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextareaField;
use skeeks\yii2\form\fields\WidgetField;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminBonusTransactionController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Бонусы');
        $this->modelClassName = ShopBonusTransaction::class;
        $this->modelShowAttribute = "asText";

        $this->generateAccessActions = false;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $result = ArrayHelper::merge(parent::actions(), [
            "index" => [
                "filters" => [
                    "visibleFilters" => [
                        //'id',
                        'shop_order_id',
                        'cms_user_id',
                    ],
                ],

                'grid' => [
                    'defaultOrder' => [
                        'created_at' => SORT_DESC,
                    ],

                    'on init' => function (\yii\base\Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;

                        $query->cmsSite();

                    },

                    'visibleColumns' => [
                        //'checkbox',
                        'actions',
                        //'id',

                        'cms_user_id',


                        'value',
                        'created_at',



                        'shop_order_id',

                        'comment',
                    ],
                    'columns'        => [

                        'created_at'  => [
                            'class'     => DateTimeColumnData::class,
                            'view_type' => DateTimeColumnData::VIEW_DATE,
                        ],
                        'cms_user_id' => [
                            'class' => UserColumnData::class,
                        ],
                        'value'      => [
                            'format' => 'raw',
                            'value' => function (ShopBonusTransaction $shopBonusTransaction) {
                                $value = Html::encode((string)$shopBonusTransaction->value);
                                if ($shopBonusTransaction->is_debit) {
                                    return Html::tag('span', '-'.$value, [
                                        'class' => 'sx-collection-cell__amount sx-text--danger',
                                    ]);
                                } else {
                                    return Html::tag('span', '+'.$value, [
                                        'class' => 'sx-collection-cell__amount sx-text--success',
                                    ]);
                                }
                            },
                        ],
                        'comment'     => [
                            'format' => 'raw',
                            'value' => function (ShopBonusTransaction $shopBonusTransaction) {
                                return Html::tag('span', Html::encode($shopBonusTransaction->comment), [
                                    'class' => 'sx-collection-cell__secondary',
                                ]);
                            },
                        ],

                        'shop_order_id' => [
                            'format' => 'raw',
                            'value' => function (ShopBonusTransaction $shopBonusTransaction) {
                                if ($shopBonusTransaction->shopOrder) {
                                    return BackendEntityLink::widget([
                                        'controllerId' => '/shop/admin-order',
                                        'modelId'      => $shopBonusTransaction->shopOrder->id,
                                        'label'        => $shopBonusTransaction->shopOrder->asText,
                                        'options'      => [
                                            'class' => 'sx-collection-cell__primary',
                                        ],
                                    ]);
                                } else {
                                    return '';
                                }
                            },
                        ],


                    ],
                ],
            ],

            "create" => [
                'fields' => [$this, 'updateFields'],

                'size'           => BackendAction::SIZE_SMALL,
                'buttons'         => ["save"],
            ],
            "update" => [
                'fields' => [$this, 'updateFields'],

                'size'           => BackendAction::SIZE_SMALL,
                'buttons'         => ["save"],
            ],

        ]);

        //ArrayHelper::remove($result, "create");
        //ArrayHelper::remove($result, "update");
        //ArrayHelper::remove($result, "delete");
        ArrayHelper::remove($result, "delete-multi");

        return $result;
    }

    public function updateFields($action)
    {
        /**
         * @var $model ShopBonusTransaction
         */
        $model = $action->model;
        $model->load(\Yii::$app->request->get());

        if ($model->isNewRecord && $model->shop_order_id) {
            if ($model->shopOrder->cms_user_id) {
                $model->cms_user_id = $model->shopOrder->cms_user_id;
            }
        }

        return [

            'cms_user_id' => [
                'class' => WidgetField::class,
                'widgetClass'  => AjaxSelectModel::class,
                'widgetConfig' => [
                    'modelClass' => CmsUser::class,
                    'options' => [],
                    'searchQuery' => function($word = '') {
                        $query = CmsUser::find()->cmsSite();
                        if ($word) {
                            $query->search($word);
                        }
                        return $query;
                    },
                ],

            ],


            'is_debit' => [
                'class' => SelectField::class,
                'allowNull' => false,
                'items' => [
                    '0' => "Начисление клиенту",
                    '1' => "Списание с клиента"
                ],
            ],

            'value'          => [
                'class' => NumberField::class,
                'append' => "Бонусов",
            ],

            'shop_order_id' => [
                'class' => WidgetField::class,
                'widgetClass'  => AjaxSelectModel::class,
                'widgetConfig' => [
                    'modelClass' => ShopOrder::class,
                    'options' => [],
                    'searchQuery' => function($word = '') {
                        $query = ShopOrder::find()->isCreated()->cmsSite();
                        if ($word) {
                            $query->search($word);
                        }
                        return $query;
                    },
                ],

            ],

            'comment'          => [
                'class' => TextareaField::class,
            ],

        ];
    }


}
