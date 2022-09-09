<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopBasket;
use skeeks\yii2\form\fields\NumberField;
use skeeks\yii2\form\fields\SelectField;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminBasketController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Cart items');
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopBasket::class;

        $this->permissionName = 'shop/admin-order';

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(),
            [
                'index' => [
                    'grid' => [
                        "columns" => [
                            [
                                'class' => \yii\grid\SerialColumn::class,
                            ],

                            [
                                'class'     => \yii\grid\DataColumn::class,
                                'attribute' => 'name',
                                'format'    => 'raw',
                                'value'     => function (\skeeks\cms\shop\models\ShopBasket $shopBasket) {
                                    $widget = new \skeeks\cms\modules\admin\widgets\AdminImagePreviewWidget([
                                        'image' => $shopBasket->product->cmsContentElement->image,
                                    ]);
                                    return $widget->run();
                                },
                            ],
                            [
                                'class'     => \yii\grid\DataColumn::class,
                                'attribute' => 'name',
                                'format'    => 'raw',
                                'value'     => function (\skeeks\cms\shop\models\ShopBasket $shopBasket) {
                                    if ($shopBasket->product) {
                                        return Html::a($shopBasket->name, $shopBasket->product->cmsContentElement->url,
                                            [
                                                'target'    => '_blank',
                                                'titla'     => \Yii::t('skeeks/shop/app', 'Watch Online'),
                                                'data-pjax' => 0,
                                            ]);
                                    } else {
                                        return $shopBasket->name;
                                    }

                                },
                            ],

                            [
                                'class'     => \yii\grid\DataColumn::class,
                                'attribute' => 'quantity',
                                'value'     => function (\skeeks\cms\shop\models\ShopBasket $shopBasket) {
                                    return $shopBasket->quantity." ".$shopBasket->measure_name;
                                },
                            ],

                            [
                                'class'     => \yii\grid\DataColumn::class,
                                'label'     => \Yii::t('skeeks/shop/app', 'Price'),
                                'attribute' => 'price',
                                'format'    => 'raw',
                                'value'     => function (\skeeks\cms\shop\models\ShopBasket $shopBasket) {
                                    if ($shopBasket->discount_value) {
                                        return "<span style='text-decoration: line-through;'>".(string)$shopBasket->moneyOriginal."</span><br />".Html::tag('small',
                                                $shopBasket->notes)."<br />".(string)$shopBasket->money."<br />".Html::tag('small',
                                                \Yii::t('skeeks/shop/app',
                                                    'Discount').": ".$shopBasket->discount_value);
                                    } else {
                                        return (string)$shopBasket->money."<br />".Html::tag('small',
                                                $shopBasket->notes);
                                    }

                                },
                            ],
                            [
                                'class'     => \yii\grid\DataColumn::class,
                                'label'     => \Yii::t('skeeks/shop/app', 'Sum'),
                                'attribute' => 'price',
                                'format'    => 'raw',
                                'value'     => function (\skeeks\cms\shop\models\ShopBasket $shopBasket) {
                                    $shopBasket->money->multiply($shopBasket->quantity);
                                    return (string)$shopBasket->money;
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
            ]
        );
    }

    public function updateFields($action)
    {
        /**
         * @var $model ShopBasket
         */
        $model = $action->model;
        if (\Yii::$app->request->get('shop_order_id') && $model->isNewRecord) {
            $model->shop_order_id = \Yii::$app->request->get('shop_order_id');
        }

        \Yii::$app->view->registerCss(<<<CSS
.field-shopbasket-shop_order_id {
    display: none;
}
CSS
        );
        
        return [
            'shop_order_id',
            'name',
            'quantity'      => [
                'class' => NumberField::class,
            ],
            'measure_name',
            'amount'        => [
                'class' => NumberField::class,
                'step' => 0.01
            ],
            'currency_code' => [
                'class' => SelectField::class,
                'items' => \yii\helpers\ArrayHelper::map(\skeeks\cms\money\models\MoneyCurrency::find()->andWhere(['is_active' => true])->all(),
                    'code', 'code'),
            ],
            'notes',
        ];
    }

}
