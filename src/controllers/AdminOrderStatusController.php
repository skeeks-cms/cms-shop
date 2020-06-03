<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopOrderStatus;
use skeeks\cms\widgets\ColorInput;
use skeeks\cms\widgets\formInputs\comboText\ComboTextInputWidget;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\TextareaField;
use skeeks\yii2\form\fields\WidgetField;
use yii\base\Event;
use yii\bootstrap\Alert;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminOrderStatusController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Order statuses');
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopOrderStatus::class;

        $this->generateAccessActions = false;

        $this->accessCallback = function () {
            if (!\Yii::$app->skeeks->site->is_default) {
                return false;
            }
            return \Yii::$app->user->can($this->uniqueId);
        };

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [

            "index" => [
                'on beforeRender' => function (Event $e) {
                    $e->content = Alert::widget([
                        'closeButton' => false,
                        'options'     => [
                            'class' => 'alert-default',
                        ],

                        'body' => <<<HTML
<p>Настройте статусы заказов вашего магазина.</p>
HTML
                        ,
                    ]);
                },

                "filters"         => false,
                "backendShowings" => false,
                'grid'            => [


                    'defaultOrder' => [
                        'priority' => SORT_ASC,
                    ],

                    'visibleColumns' => [
                        'checkbox',
                        'actions',
                        /*'id',*/

                        'name',

                        //'description',

                        'priority',

                    ],
                    'columns'        => [
                        'name' => [
                            'value' => function (ShopOrderStatus $shopOrderStatus) {
                                return \yii\helpers\Html::a($shopOrderStatus->name, null, [
                                    'style' => "background: {$shopOrderStatus->bg_color}; color: {$shopOrderStatus->color}; border-radius: 3px; padding-left: 5px; padding-right: 5px;",
                                    'class' => "sx-trigger-action",
                                    'href'  => "#",
                                ]) . "<br /><span style='color: gray'>" . $shopOrderStatus->description . "</span>";
                            },
                        ],
                    ],

                ],
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
        /**
         * @var $model ShopTypePrice
         */
        $model = $action->model;

        $model->load(\Yii::$app->request->get());
        return [
            'main' => [
                'class'  => FieldSet::class,
                'name'   => 'Основное',
                'fields' => [
                    'name',
                    'priority' => [
                        'class' => NumberField::class,
                    ],

                    'description' => [
                        'class' => TextareaField::class,
                    ],

                    'color' => [
                        'class' => WidgetField::class,
                        'widgetClass' => ColorInput::class,
                    ],
                    'bg_color' => [
                        'class' => WidgetField::class,
                        'widgetClass' => ColorInput::class,
                    ],

                ],
            ],


            'other' => [
                'class'  => FieldSet::class,
                'name'   => 'Дополнительно',
                'fields' => [
                    'is_comment_required' => [
                        'class' => BoolField::class,
                        'allowNull' => false,
                    ],
                    'client_available_statuses' => [
                        'class' => SelectField::class,
                        'multiple' => true,
                        'items' => ArrayHelper::map(ShopOrderStatus::find()->all(), 'id', 'name'),
                    ],
                ]
            ],

            'pay' => [
                'class'  => FieldSet::class,
                'name'   => 'Оплата',
                'fields' => [
                    'is_payment_allowed' => [
                        'class' => BoolField::class,
                        'allowNull' => false
                    ],

                ]
            ],
            

            
            'additional' => [
                'class'  => FieldSet::class,
                'name'   => 'Дополнительные подписи',
                'fields' => [
                    'btn_name',
                    'order_page_description' => [
                        'class' => WidgetField::class,
                        'widgetClass' => ComboTextInputWidget::class,
                    ],

                    'email_notify_description' => [
                        'class' => WidgetField::class,
                        'widgetClass' => ComboTextInputWidget::class,
                    ],
                ]
            ],

            'auto' => [
                'class'  => FieldSet::class,
                'name'   => 'Автосмена',
                'fields' => [

                    'auto_next_shop_order_status_id' => [
                        'class' => SelectField::class,
                        'items' => ArrayHelper::map(
                            ShopOrderStatus::find()->all(),
                            'id',
                            'name'
                        )
                    ],

                    'auto_next_status_time' => [
                        'class' => NumberField::class
                    ]

                ]
            ],
        ];
    }
}
