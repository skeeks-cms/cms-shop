<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\models\ShopViewedProduct;
use yii\helpers\ArrayHelper;
use yii\helpers\UnsetArrayValue;

/**
 * Class AdminOrderStatusController
 * @package skeeks\cms\shop\controllers
 */
class AdminViewedProductController extends BackendModelStandartController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Viewed products');
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopViewedProduct::class;

        $this->generateAccessActions = false;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [

            "index" => [
                "filters" => [
                    "visibleFilters" => [
                        'id',
                    ],
                ],
                'grid'    => [
                    'defaultOrder' => [
                        //'is_created' => SORT_DESC,
                        'created_at' => SORT_DESC,
                    ],
                    'visibleColumns' => [
                        'checkbox',
                        'actions',
                        'created_at',
                        'shop_fuser_id',
                        'shop_product_id',
                    ],
                    'columns'        => [
                        'created_at' => [
                            'class' => DateTimeColumnData::class
                        ],
                        'shop_fuser_id' => [
                            'format' => 'raw',
                            'label'  => \Yii::t('skeeks/shop/app', 'User'),
                            'value'  => function (\skeeks\cms\shop\models\ShopViewedProduct $shopViewedProduct) {
                                return $shopViewedProduct->shopFuser->cmsUser ? (new \skeeks\cms\shop\widgets\AdminBuyerUserWidget(['user' => $shopViewedProduct->shopFuser->cmsUser]))->run() : \Yii::t('skeeks/shop/app',
                                    'Not authorized');
                            },
                        ],
                        'shop_product_id' => [
                            'format' => 'raw',
                            'label'  => \Yii::t('skeeks/shop/app', 'Good'),
                            'value'  => function (\skeeks\cms\shop\models\ShopViewedProduct $shopViewedProduct) {
                                if ($shopViewedProduct->shopProduct) {

                                    return (new \skeeks\cms\modules\admin\widgets\AdminImagePreviewWidget([
                                            'image'    => $shopViewedProduct->shopProduct->cmsContentElement->image,
                                            'maxWidth' => "25px",
                                        ]))->run()." ".\yii\helpers\Html::a($shopViewedProduct->shopProduct->cmsContentElement->name,
                                            $shopViewedProduct->shopProduct->cmsContentElement->url, [
                                                'target'    => "_blank",
                                                'data-pjax' => 0,
                                            ]);
                                }

                                return null;
                            },
                        ],

                    ]
                ]
            ],

            'create' => new UnsetArrayValue(),
            'update' => new UnsetArrayValue()
        ]);
    }

}
