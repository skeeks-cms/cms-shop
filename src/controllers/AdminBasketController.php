<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\models\CmsAgent;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\models\ShopBasket;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Class AdminBasketController
 * @package skeeks\cms\shop\controllers
 */
class AdminBasketController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Cart items');
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopBasket::className();

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(),
            [
                'index' =>
                    [
                        "gridConfig" =>
                            [
                                /*'settingsData' =>
                                [
                                    'order' => SORT_ASC,
                                    'orderBy' => "priority",
                                ]*/
                            ],

                        "columns" => [
                            [
                                'class' => \yii\grid\SerialColumn::className(),
                            ],

                            [
                                'class'     => \yii\grid\DataColumn::className(),
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
                                'class'     => \yii\grid\DataColumn::className(),
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
                                'class'     => \yii\grid\DataColumn::className(),
                                'attribute' => 'quantity',
                                'value'     => function (\skeeks\cms\shop\models\ShopBasket $shopBasket) {
                                    return $shopBasket->quantity." ".$shopBasket->measure_name;
                                },
                            ],

                            [
                                'class'     => \yii\grid\DataColumn::className(),
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
                                'class'     => \yii\grid\DataColumn::className(),
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
            ]
        );
    }

}
