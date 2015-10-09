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
                    },


                    "columns"      => [
                        [
                            'class'         => UpdatedAtColumn::className(),
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'filter'        => false,
                            'format'        => raw,
                            'label'         => 'Пользователь',
                            'value'         => function(ShopFuser $model)
                            {
                                return $model->user ? ( new AdminBuyerUserWidget(['user' => $model->user]) )->run() : "Неавторизован";
                            },
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'filter'        => false,
                            'format'        => 'raw',
                            'label'         => 'Профиль покупателя',
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
                            'label'         => 'Тип профиля',
                            'value'         => function(ShopFuser $model)
                            {
                                return $model->personType->name;
                            },
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'filter'        => false,
                            'label'         => 'Цена корзины',
                            'value'         => function(ShopFuser $model)
                            {
                                return \Yii::$app->money->intlFormatter()->format($model->money);
                            },
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'filter'        => false,
                            'label'         => 'Количество наименований',
                            'value'         => function(ShopFuser $model)
                            {
                                return $model->countShopBaskets;
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
                                        $result[] = Html::a($shopBasket->name, $shopBasket->product->cmsContentElement->url, ['target' => '_blank']) . <<<HTML
 ($shopBasket->quantity $shopBasket->measure_name) — {$money}
HTML;

                                    }
                                    return implode('<hr />', $result);
                                }
                            },
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'filter'        => ArrayHelper::map(CmsSite::find()->active()->all(), 'id', 'name'),
                            'attribute'     => 'site_id',
                            'format'        => 'raw',
                            'label'         => 'Сайт',
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
    }

}
