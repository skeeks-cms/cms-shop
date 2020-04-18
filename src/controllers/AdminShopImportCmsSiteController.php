<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\events\ViewRenderEvent;
use skeeks\cms\backend\grid\DefaultActionColumn;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopImportCmsSite;
use skeeks\cms\shop\models\ShopSite;
use skeeks\cms\shop\models\ShopTypePrice;
use skeeks\cms\widgets\formInputs\selectTree\SelectTreeInputWidget;
use skeeks\yii2\form\fields\FieldSet;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\SelectField;
use skeeks\yii2\form\fields\WidgetField;
use yii\base\Event;
use yii\bootstrap\Alert;
use yii\helpers\ArrayHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminShopImportCmsSiteController extends BackendModelStandartController
{

    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Поставщики');
        $this->modelShowAttribute = "asText";
        $this->modelClassName = ShopImportCmsSite::class;

        $this->generateAccessActions = false;

        $this->accessCallback = function () {
            //Если это сайт по умолчанию, этот раздел не показываем
            if (\Yii::$app->skeeks->site->is_default) {
                return false;
            }

            /**
             * @var ShopSite $shopSite
             */
            $shopSite = \Yii::$app->skeeks->site->shopSite;
            if (!$shopSite) {
                return false;
            }

            /**
             *
             */
            if ($shopSite->is_supplier) {
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
                'on beforeRender' => function (ViewRenderEvent $e) {

                    if (!\Yii::$app->skeeks->site->shopImportCmsSites) {
                        $e->isRenderContent = false;

                        \Yii::$app->view->registerJs(<<<JS
$(".sx-content-actions [data-id=create]").popover({
'content': 'Добавьте первого поставщика!', 
'trigger': 'focus', 
'container': false
});
_.delay(function() {
    $(".sx-content-actions [data-id=create]").popover("show");    
}, 1000);
JS

                        );
                    }


                    //$e->isRenderContent = false;
                    $e->content = Alert::widget([
                        'closeButton' => false,
                        'options'     => [
                            'class' => 'alert-default',
                        ],

                        'body' => <<<HTML
В этом разделе вы можете настроить автоматический сбор товаров на сайт от поставщиков.
HTML
                        ,
                    ]);
                },
                "filters"         => false,
                'grid'            => [
                    'on init' => function (Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;

                        $query->andWhere(['cms_site_id' => \Yii::$app->skeeks->site->id]);
                    },

                    'defaultOrder' => [
                        'priority' => SORT_ASC,
                    ],

                    'visibleColumns' => [
                        'checkbox',
                        'actions',

                        ///'id',

                        'sender_cms_site_id',
                        'priority',
                    ],
                    'columns'        => [
                        'sender_cms_site_id' => [
                            'value' => function(ShopImportCmsSite $model) {
                                return \yii\helpers\Html::a($model->senderCmsSite->asText, "#", [
                                    'class' => "sx-trigger-action",
                                ]);
                            }
                        ],
                    ],

                ],
            ],

            "create" => [
                'on beforeRender' => function (ViewRenderEvent $e) {

                    $e->content = Alert::widget([
                        'closeButton' => false,
                        'options'     => [
                            'class' => 'alert-default',
                        ],

                        'body' => <<<HTML
В этом разделе вы можете настроить автоматический сбор товаров на сайт от поставщиков.
HTML
                        ,
                    ]);
                },
                'fields'          => [$this, 'updateFields'],
            ],

            "update" => [
                'fields' => [$this, 'updateFields'],
            ],

        ]);
    }

    /**
     * @param $action
     * @return array
     */
    public function updateFields($action)
    {
        /**
         * @var $model ShopImportCmsSite
         */
        $model = $action->model;

        $model->load(\Yii::$app->request->get());

        $result = [
            'supplier' => [
                'class'  => FieldSet::class,
                'name'   => 'Поставщик',
                'fields' => [
                    'sender_cms_site_id'        => [
                        'class'          => SelectField::class,
                        'items'          => ArrayHelper::map(
                            ShopSite::find()->where(['is_supplier' => 1])->all(),
                            'id',
                            'asText'
                        ),
                        'elementOptions' => [
                            RequestResponse::DYNAMIC_RELOAD_FIELD_ELEMENT => 'true',
                        ],
                    ],
                    'sender_shop_type_price_id' => [
                        'class' => SelectField::class,
                        'items' => ArrayHelper::map(
                            ShopTypePrice::find()->where(['cms_site_id' => $model->sender_cms_site_id ? $model->sender_cms_site_id : null])->all(),
                            'id',
                            'asText'
                        ),
                    ],
                ],
            ],

            'site' => [
                'class'  => FieldSet::class,
                'name'   => 'Ваш сайт',
                'fields' => [
                    "extra_charge"                => [
                        'class'  => NumberField::class,
                        'append' => "%",
                    ],
                    "priority"                => [
                        'class'  => NumberField::class,
                    ],
                    /*'receiver_shop_type_price_id' => [
                        'class' => SelectField::class,
                        'items' => ArrayHelper::map(
                            \Yii::$app->skeeks->site->shopTypePrices,
                            'id',
                            'asText'
                        ),
                    ],
                    'receiver_cms_tree_id' => [
                        'class' => WidgetField::class,
                        'widgetClass' => SelectTreeInputWidget::class,
                        'widgetConfig' => [
                            'treeWidgetOptions' => [
                                'models' => \skeeks\cms\models\CmsTree::findRootsForSite()->all(),
                            ],
                        ],
                    ]*/
                ],
            ],


        ];

        return $result;
    }

}
