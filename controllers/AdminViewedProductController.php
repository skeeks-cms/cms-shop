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
use skeeks\cms\grid\CreatedByColumn;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContent;
use skeeks\cms\modules\admin\actions\modelEditor\AdminMultiModelEditAction;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\models\ShopOrderStatus;
use skeeks\cms\shop\models\ShopPersonType;
use skeeks\cms\shop\models\ShopViewedProduct;
use yii\grid\DataColumn;
use yii\helpers\ArrayHelper;

/**
 * Class AdminOrderStatusController
 * @package skeeks\cms\shop\controllers
 */
class AdminViewedProductController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name                     = "Просмотренные товары";
        $this->modelShowAttribute       = "name";
        $this->modelClassName           = ShopViewedProduct::className();

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(),
            [
                'create' =>
                [
                    'visible' => false
                ],

                'update' =>
                [
                    'visible' => false
                ],

                'system' =>
                [
                    'visible' => false
                ],

                'index' =>
                [
                    "columns"      => [
                        [
                            'class' => CreatedAtColumn::className(),
                            'label' => "Дата просмотра",
                        ],
                        [
                            'class' => DataColumn::className(),
                            'label' => "Пользователь",
                            'value' => function(ShopViewedProduct $shopViewedProduct)
                            {
                                if ($shopViewedProduct->shopFuser->user)
                                {
                                    return $shopViewedProduct->shopFuser->user->displayName;
                                }

                                return "Неавторизован [{$shopViewedProduct->shopFuser->id}]";
                            },
                        ],

                        [
                            'class' => DataColumn::className(),
                            'label' => "Товар",
                            'value' => function(ShopViewedProduct $shopViewedProduct)
                            {
                                if ($shopViewedProduct->shopProduct)
                                {
                                    return $shopViewedProduct->shopProduct->cmsContentElement->name;
                                }

                                return null;
                            },
                        ],

                    ],
                ]
            ]
        );
    }

}
