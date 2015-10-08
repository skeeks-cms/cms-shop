<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
namespace skeeks\cms\shop\controllers;

use skeeks\cms\components\Cms;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\grid\CreatedAtColumn;
use skeeks\cms\grid\SiteColumn;
use skeeks\cms\grid\UserColumnData;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsSite;
use skeeks\cms\modules\admin\actions\modelEditor\AdminMultiModelEditAction;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\models\ShopAffiliate;
use skeeks\cms\shop\models\ShopAffiliatePlan;
use skeeks\cms\shop\models\ShopContent;
use skeeks\cms\shop\models\ShopExtra;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\shop\models\ShopOrderStatus;
use skeeks\cms\shop\models\ShopPersonType;
use skeeks\cms\shop\models\ShopTax;
use skeeks\cms\shop\models\ShopVat;
use yii\grid\DataColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Class AdminExtraController
 * @package skeeks\cms\shop\controllers
 */
class AdminOrderController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name                     = "Заказы";
        $this->modelShowAttribute       = "id";
        $this->modelClassName           = ShopOrder::className();

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
                    "columns"               => [
                        'id',

                        [
                            'class'     => DataColumn::className(),
                            'attribute'     => 'status_code',
                            'format'     => 'raw',
                            'filter'     => ArrayHelper::map(ShopOrderStatus::find()->all(), 'code', 'name'),
                            'value'     => function(ShopOrder $order)
                            {
                                return Html::label($order->status->name, null, [
                                    'style' => "background: {$order->status->color}",
                                    'class' => "label"
                                ]) . "<br />" .
                                    Html::tag("small", \Yii::$app->formatter->asDatetime($order->status_at) . " (" . \Yii::$app->formatter->asRelativeTime($order->status_at) . ")")
                                ;
                            }
                        ],

                        /*[
                            'class'     => DataColumn::className(),
                            'attribute' => 'buyer_id',
                            'format'    => 'raw',
                            'value'     => function(ShopOrder $model)
                            {
                                if (!$model->buyer)
                                {
                                    return null;
                                }

                                return Html::a($model->buyer->name . " [{$model->buyer->id}]", UrlHelper::construct('shop/admin-buyer/related-properties', ['pk' => $model->buyer->id])->enableAdmin()->toString());
                            }
                        ],*/

                        [
                            'class'     => BooleanColumn::className(),
                            'attribute'     => 'payed',
                            'format'     => 'raw',
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'attribute'     => "user_id",
                            'label'         => "Покупатель",
                            'format'        => "raw",
                            'value'         => function(ShopOrder $shopOrder)
                            {
                               return (new \skeeks\cms\shop\widgets\AdminBuyerUserWidget(['user' => $shopOrder->user]))->run();
                            },
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'filter'        => false,
                            'format'        => 'raw',
                            'label'         => 'Товар',
                            'value'         => function(ShopOrder $model)
                            {
                                if ($model->shopBaskets)
                                {
                                    $result = [];
                                    foreach ($model->shopBaskets as $shopBasket)
                                    {
                                        $money = \Yii::$app->money->intlFormatter()->format($shopBasket->money);
                                        $result[] = Html::a($shopBasket->name, $shopBasket->product->cmsContentElement->url, ['target' => '_blank']) . <<<HTML
  — $shopBasket->quantity $shopBasket->measure_name
HTML;

                                    }
                                    return implode('<hr />', $result);
                                }
                            },
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'format'        => 'raw',
                            'attribute'     => 'price',
                            'label'         => 'Сумма',
                            'value'         => function(ShopOrder $model)
                            {
                                return \Yii::$app->money->intlFormatter()->format($model->money);
                            },
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'filter'        => ArrayHelper::map(CmsSite::find()->active()->all(), 'id', 'name'),
                            'attribute'     => 'site_id',
                            'format'        => 'raw',
                            'label'         => 'Сайт',
                            'value'         => function(ShopOrder $model)
                            {
                                return $model->site->name . " [{$model->site->code}]";
                            },
                        ],

                        [
                            'class'     => CreatedAtColumn::className(),
                        ],

                    ],
                ],

            ]
        );
    }

}
