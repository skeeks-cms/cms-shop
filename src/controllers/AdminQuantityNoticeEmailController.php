<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopQuantityNoticeEmail;
use yii\helpers\ArrayHelper;
use yii\helpers\UnsetArrayValue;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminQuantityNoticeEmailController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Notification of receipt products by email');
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopQuantityNoticeEmail::class;

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

                        'email',
                        'name',
                        'good',

                        'is_notified',

                        'notified_at',
                        'user',
                    ],
                    'columns'        => [
                        'created_at'  => [
                            'class' => DateTimeColumnData::class,
                        ],
                        'notified_at' => [
                            'class' => DateTimeColumnData::class,
                        ],

                        'is_notified' => [
                            'class' => BooleanColumn::class,
                        ],

                        'good' => [
                            'format' => 'raw',
                            'label'  => \Yii::t('skeeks/shop/app', 'Good'),
                            'value'  => function (\skeeks\cms\shop\models\ShopQuantityNoticeEmail $shopQuantityNoticeEmail) {
                                if ($shopQuantityNoticeEmail->shopProduct) {
                                    return (new \skeeks\cms\modules\admin\widgets\AdminImagePreviewWidget([
                                            'image'    => $shopQuantityNoticeEmail->shopProduct->cmsContentElement->image,
                                            'maxWidth' => "25px",
                                        ]))->run()." ".\yii\helpers\Html::a($shopQuantityNoticeEmail->shopProduct->cmsContentElement->name,
                                            $shopQuantityNoticeEmail->shopProduct->cmsContentElement->url, [
                                                'target'    => "_blank",
                                                'data-pjax' => 0,
                                            ])."<br /><small>".\Yii::t('skeeks/shop/app',
                                            'In stock').": ".$shopQuantityNoticeEmail->shopProduct->quantity."</small>";
                                }

                                return null;
                            },
                        ],
                        'user' => [
                            'format' => 'raw',
                            'label'  => \Yii::t('skeeks/shop/app', 'User'),
                            'value'  => function (\skeeks\cms\shop\models\ShopQuantityNoticeEmail $shopQuantityNoticeEmail) {
                                return ($shopQuantityNoticeEmail->shopUser && $shopQuantityNoticeEmail->shopUser->cmsUser ? (new \skeeks\cms\shop\widgets\AdminBuyerUserWidget(['user' => $shopQuantityNoticeEmail->shopUser->cmsUser]))->run() : \Yii::t('skeeks/shop/app',
                                    'Not authorized'));
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
