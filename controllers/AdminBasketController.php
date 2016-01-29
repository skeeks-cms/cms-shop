<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
namespace skeeks\cms\shop\controllers;

use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContent;
use skeeks\cms\modules\admin\actions\modelEditor\AdminMultiModelEditAction;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\models\ShopBasket;
use skeeks\cms\shop\models\ShopOrderStatus;
use yii\grid\DataColumn;
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
        $this->name                     = 'Позиции корзины';
        $this->modelShowAttribute       = "name";
        $this->modelClassName           = ShopBasket::className();

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

                    "columns"      => [
                        [
                            'class' => \yii\grid\SerialColumn::className()
                        ],

                        [
                            'class'     => \yii\grid\DataColumn::className(),
                            'attribute' => 'name',
                            'format'    => 'raw',
                            'value'     => function(\skeeks\cms\shop\models\ShopBasket $shopBasket)
                            {
                                $widget = new \skeeks\cms\modules\admin\widgets\AdminImagePreviewWidget([
                                    'image' => $shopBasket->product->cmsContentElement->image
                                ]);
                                return $widget->run();
                            }
                        ],
                        [
                            'class' => \yii\grid\DataColumn::className(),
                            'attribute' => 'name',
                            'format' => 'raw',
                            'value' => function(\skeeks\cms\shop\models\ShopBasket $shopBasket)
                            {
                                if ($shopBasket->product)
                                {
                                    return Html::a($shopBasket->name, $shopBasket->product->cmsContentElement->url, [
                                        'target' => '_blank',
                                        'titla' => "Смотреть на сайте",
                                        'data-pjax' => 0
                                    ]);
                                } else
                                {
                                    return $shopBasket->name;
                                }

                            }
                        ],

                        [
                            'class' => \yii\grid\DataColumn::className(),
                            'attribute' => 'quantity',
                            'value' => function(\skeeks\cms\shop\models\ShopBasket $shopBasket)
                            {
                                return $shopBasket->quantity . " " . $shopBasket->measure_name;
                            }
                        ],

                        [
                            'class' => \yii\grid\DataColumn::className(),
                            'label' => \skeeks\cms\shop\Module::t('app', 'Price'),
                            'attribute' => 'price',
                            'format' => 'raw',
                            'value' => function(\skeeks\cms\shop\models\ShopBasket $shopBasket)
                            {
                                if ($shopBasket->discount_value)
                                {
                                    return "<span style='text-decoration: line-through;'>" . \Yii::$app->money->intlFormatter()->format($shopBasket->moneyOriginal) . "</span><br />". Html::tag('small', $shopBasket->notes) . "<br />" . \Yii::$app->money->intlFormatter()->format($shopBasket->money) . "<br />" . Html::tag('small', \skeeks\cms\shop\Module::t('app', 'Discount').": " . $shopBasket->discount_value);
                                } else
                                {
                                    return \Yii::$app->money->intlFormatter()->format($shopBasket->money) . "<br />" . Html::tag('small', $shopBasket->notes);
                                }

                            }
                        ],
                        [
                            'class' => \yii\grid\DataColumn::className(),
                            'label' => \skeeks\cms\shop\Module::t('app', 'Sum'),
                            'attribute' => 'price',
                            'format' => 'raw',
                            'value' => function(\skeeks\cms\shop\models\ShopBasket $shopBasket)
                            {
                                return \Yii::$app->money->intlFormatter()->format($shopBasket->money->multiply($shopBasket->quantity));
                            }
                        ],
                    ],
                ]
            ]
        );
    }

}
