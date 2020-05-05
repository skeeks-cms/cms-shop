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
use skeeks\cms\shop\models\ShopViewedProduct;
use yii\helpers\ArrayHelper;
use yii\helpers\UnsetArrayValue;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminViewedProductController extends BackendModelStandartController
{
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
                    'defaultOrder'   => [
                        //'is_created' => SORT_DESC,
                        'created_at' => SORT_DESC,
                    ],
                    'visibleColumns' => [
                        'checkbox',
                        'actions',
                        'created_at',
                        'shop_user_id',
                        'shop_product_id',
                    ],
                    'columns'        => [
                        'created_at'      => [
                            'class' => DateTimeColumnData::class,
                        ],
                        'shop_user_id'   => [
                            'format' => 'raw',
                            'label'  => \Yii::t('skeeks/shop/app', 'User'),
                            'value'  => function (\skeeks\cms\shop\models\ShopViewedProduct $shopViewedProduct) {
                                return $shopViewedProduct->shopUser->cmsUser ? (new \skeeks\cms\shop\widgets\AdminBuyerUserWidget(['user' => $shopViewedProduct->shopUser->cmsUser]))->run() : \Yii::t('skeeks/shop/app',
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

                    ],
                ],
            ],

            'create' => new UnsetArrayValue(),
            'update' => new UnsetArrayValue(),
        ]);
    }

}
