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
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsSite;
use skeeks\cms\modules\admin\actions\modelEditor\AdminMultiModelEditAction;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\components\CartComponent;
use skeeks\cms\shop\models\ShopFuser;
use skeeks\cms\shop\models\ShopOrderStatus;
use skeeks\cms\shop\models\ShopPersonType;
use skeeks\cms\shop\models\ShopTax;
use skeeks\cms\shop\models\ShopVat;
use skeeks\cms\shop\widgets\AdminBuyerUserWidget;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
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
        $this->name                     = \Yii::t('skeeks/shop/app', 'Baskets');
        $this->modelShowAttribute       = "name";
        $this->modelClassName           = ShopFuser::className();

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = ArrayHelper::merge(parent::actions(),
            [
                'index' =>
                [
                    "dataProviderCallback" => function(ActiveDataProvider $dataProvider)
                    {
                        $query = $dataProvider->query;
                        /**
                         * @var ActiveQuery $query
                         */
                        //$query->select(['app_company.*', 'count(`app_company_officer_user`.`id`) as countOfficer']);
                        $query->groupBy(['shop_fuser.id']);

                        $query->with('user');
                        $query->with('personType');
                        $query->with('buyer');
                        $query->with('shopBaskets');
                        $query->with('shopBaskets.product');

                        $query->joinWith('shopBaskets as sb');
                        $query->andWhere(
                            [
                                'or',
                                ['>=', 'sb.id', 0],
                                ['>=', 'shop_fuser.user_id', 0],
                                ['>=', 'shop_fuser.person_type_id', 0],
                                ['>=', 'shop_fuser.buyer_id', 0]
                            ]
                        );

                        //$query->orderBy(['sb.updated_at' => SORT_DESC]);
                        $query->orderBy(['shop_fuser.updated_at' => SORT_DESC]);
                    },


                    "columns"      => [
                        [
                            'class'         => UpdatedAtColumn::className(),
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'filter'        => false,
                            'format'        => 'raw',
                            'label'         => \Yii::t('skeeks/shop/app', 'User'),
                            'value'         => function(ShopFuser $model)
                            {
                                return $model->user ? ( new AdminBuyerUserWidget(['user' => $model->user]) )->run() : \Yii::t('skeeks/shop/app', 'Not authorized');
                            },
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'filter'        => false,
                            'format'        => 'raw',
                            'label'         => \Yii::t('skeeks/shop/app', 'Profile of buyer'),
                            'value'         => function(ShopFuser $model)
                            {
                                if (!$model->buyer)
                                {
                                    return null;
                                }

                                return Html::a($model->buyer->name . " [{$model->buyer->id}]", UrlHelper::construct('shop/admin-buyer/related-properties', ['pk' => $model->buyer->id])->enableAdmin()->toString());
                            },
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'filter'        => ArrayHelper::map(ShopPersonType::find()->active()->all(), 'id', 'name'),
                            'attribute'     => 'person_type_id',
                            'label'         => \Yii::t('skeeks/shop/app', 'Profile type'),
                            'value'         => function(ShopFuser $model)
                            {
                                return $model->personType ? $model->personType->name : "";
                            },
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'filter'        => false,
                            'label'         => \Yii::t('skeeks/shop/app', 'Price of basket'),
                            'value'         => function(ShopFuser $model)
                            {
                                return \Yii::$app->money->intlFormatter()->format($model->money);
                            },
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'filter'        => false,
                            'label'         => \Yii::t('skeeks/shop/app', 'Number of items'),
                            'value'         => function(ShopFuser $model)
                            {
                                return $model->countShopBaskets;
                            },
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'filter'        => false,
                            'format'        => 'raw',
                            'label'         => \Yii::t('skeeks/shop/app', 'Good'),
                            'value'         => function(ShopFuser $model)
                            {
                                if ($model->shopBaskets)
                                {
                                    $result = [];
                                    foreach ($model->shopBaskets as $shopBasket)
                                    {
                                        $money = \Yii::$app->money->intlFormatter()->format($shopBasket->money);
                                        $result[] = Html::a($shopBasket->name, $shopBasket->product->cmsContentElement->url, ['target' => '_blank']) . <<<HTML
 ($shopBasket->quantity $shopBasket->measure_name) — {$money}
HTML;

                                    }
                                    return implode('<hr style="margin: 0px;"/>', $result);
                                }
                            },
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'filter'        => ArrayHelper::map(CmsSite::find()->active()->all(), 'id', 'name'),
                            'attribute'     => 'site_id',
                            'format'        => 'raw',
                            'visible'       => false,
                            'label'         => \Yii::t('skeeks/shop/app', 'Site'),
                            'value'         => function(ShopFuser $model)
                            {
                                return $model->site->name . " [{$model->site->code}]";
                            },
                        ],

                        [
                            'class'         => CreatedAtColumn::className(),
                        ]
                    ],
                ]
            ]

        );

        unset($actions['create']);
        unset($actions['update']);

        return $actions;
    }

}
