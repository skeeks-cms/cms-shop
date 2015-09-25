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
use skeeks\cms\grid\UpdatedAtColumn;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContent;
use skeeks\cms\modules\admin\actions\modelEditor\AdminMultiModelEditAction;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\components\CartComponent;
use skeeks\cms\shop\models\ShopFuser;
use skeeks\cms\shop\models\ShopOrderStatus;
use skeeks\cms\shop\models\ShopPersonType;
use skeeks\cms\shop\models\ShopTax;
use skeeks\cms\shop\models\ShopVat;
use yii\grid\DataColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Class AdminFuserController
 * @package skeeks\cms\shop\controllers
 */
class AdminFuserController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name                     = "Корзины";
        $this->modelShowAttribute       = "name";
        $this->modelClassName           = ShopFuser::className();

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
                    "columns"      => [
                        [
                            'class'         => UpdatedAtColumn::className(),
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'filter'        => false,
                            'label'         => 'Покупатель',
                            'value'         => function(ShopFuser $model)
                            {
                                return $model->user ? $model->user->displayName : "Неавторизован";
                            },
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'filter'        => false,
                            'label'         => 'Цена корзины',
                            'value'         => function(ShopFuser $model)
                            {
                                $cart = new CartComponent();
                                $cart->shopFuser = $model;
                                return \Yii::$app->money->intlFormatter()->format($cart->money);
                            },
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'filter'        => false,
                            'label'         => 'Количество наименований',
                            'value'         => function(ShopFuser $model)
                            {
                                $cart = new CartComponent();
                                $cart->shopFuser = $model;
                                return $cart->countShopBaskets;
                            },
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'filter'        => false,
                            'format'        => 'raw',
                            'label'         => 'Товар',
                            'value'         => function(ShopFuser $model)
                            {
                                if ($model->shopBaskets)
                                {
                                    $result = [];
                                    foreach ($model->shopBaskets as $shopBasket)
                                    {
                                        $money = \Yii::$app->money->intlFormatter()->format($shopBasket->money);
                                        $result[] = Html::a($shopBasket->product->cmsContentElement->name, $shopBasket->product->cmsContentElement->url, ['target' => '_blank']) . <<<HTML
 ($shopBasket->quantity $shopBasket->measure_name) — {$money}
HTML;

                                    }
                                    return implode('<hr />', $result);
                                }
                            },
                        ],

                        [
                            'class'         => CreatedAtColumn::className(),
                        ]
                    ],
                ]
            ]
        );
    }

}
