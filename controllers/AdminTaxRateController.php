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
use skeeks\cms\grid\SiteColumn;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContent;
use skeeks\cms\modules\admin\actions\modelEditor\AdminMultiModelEditAction;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\models\ShopOrderStatus;
use skeeks\cms\shop\models\ShopPersonType;
use skeeks\cms\shop\models\ShopTax;
use skeeks\cms\shop\models\ShopTaxRate;
use skeeks\cms\shop\models\ShopVat;
use yii\grid\DataColumn;
use yii\helpers\ArrayHelper;

/**
 * Class AdminTaxController
 * @package skeeks\cms\shop\controllers
 */
class AdminTaxRateController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name                     = \Yii::t('skeeks/shop/app', 'Tax rates');
        $this->modelShowAttribute       = "id";
        $this->modelClassName           = ShopTaxRate::className();

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
                        'value',

                        [
                            'class'         => DataColumn::className(),
                            'value'         => function(ShopTaxRate $model)
                            {
                                return $model->tax->name . " (" . $model->tax->site->name . ")";
                            },
                            'attribute'     => "tax_id"
                        ],
                        [
                            'class'         => DataColumn::className(),
                            'value'         => function(ShopTaxRate $model)
                            {
                                return $model->personType->name;
                            },
                            'attribute'     => "person_type_id"
                        ],

                        [
                            'class'         => BooleanColumn::className(),
                            'attribute'     => "is_in_price"
                        ],

                        [
                            'class'         => BooleanColumn::className(),
                            'attribute'     => "active"
                        ],

                        [
                            'attribute'     => "priority"
                        ]
                    ],
                ]
            ]
        );
    }

}
