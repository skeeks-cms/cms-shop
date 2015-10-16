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
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\grid\SiteColumn;
use skeeks\cms\grid\UpdatedAtColumn;
use skeeks\cms\grid\UpdatedByColumn;
use skeeks\cms\grid\UserColumnData;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContent;
use skeeks\cms\modules\admin\actions\modelEditor\AdminMultiModelEditAction;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\models\ShopAffiliate;
use skeeks\cms\shop\models\ShopAffiliatePlan;
use skeeks\cms\shop\models\ShopContent;
use skeeks\cms\shop\models\ShopDiscount;
use skeeks\cms\shop\models\ShopExtra;
use skeeks\cms\shop\models\ShopOrderStatus;
use skeeks\cms\shop\models\ShopPersonType;
use skeeks\cms\shop\models\ShopTax;
use skeeks\cms\shop\models\ShopVat;
use skeeks\modules\cms\money\Money;
use yii\data\ActiveDataProvider;
use yii\grid\DataColumn;
use yii\helpers\ArrayHelper;

/**
 * Class AdminExtraController
 * @package skeeks\cms\shop\controllers
 */
class AdminDiscountController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name                     = skeeks\cms\shop\Module::t('app', 'Discount goods');
        $this->modelShowAttribute       = "id";
        $this->modelClassName           = ShopDiscount::className();

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
                    "dataProviderCallback" => function(ActiveDataProvider $activeDataProvider)
                    {
                        $activeDataProvider->query->andWhere(['type' => ShopDiscount::TYPE_DEFAULT]);
                    },
                    "columns"               => [
                        'id',

                        [
                            'attribute' => 'name',
                        ],

                        [
                            'attribute'     => 'value',
                            'class'         => DataColumn::className(),
                            'value' => function(ShopDiscount $shopDiscount)
                            {
                                if ($shopDiscount->value_type == ShopDiscount::VALUE_TYPE_P)
                                {
                                    return \Yii::$app->formatter->asPercent($shopDiscount->value / 100);
                                } else
                                {
                                    $money = Money::fromString((string) $shopDiscount->value, $shopDiscount->currency_code);
                                    return \Yii::$app->money->intlFormatter()->format($money);
                                }
                            },
                        ],

                        [
                            'attribute'     => 'active',
                            'class'         => BooleanColumn::className(),
                        ],

                        [
                            'attribute'     => 'active_from',
                            'class'         => DateTimeColumnData::className(),
                        ],

                        [
                            'attribute'     => 'active_to',
                            'class'         => DateTimeColumnData::className(),
                        ],

                        [
                            'class' => UpdatedByColumn::className()
                        ],

                        [
                            'class' => UpdatedAtColumn::className()
                        ]
                    ],
                ],

            ]
        );
    }

}
