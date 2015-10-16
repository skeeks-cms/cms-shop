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
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContent;
use skeeks\cms\modules\admin\actions\modelEditor\AdminMultiModelEditAction;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\models\ShopAffiliate;
use skeeks\cms\shop\models\ShopAffiliatePlan;
use skeeks\cms\shop\models\ShopContent;
use skeeks\cms\shop\models\ShopExtra;
use skeeks\cms\shop\models\ShopOrderStatus;
use skeeks\cms\shop\models\ShopPersonType;
use skeeks\cms\shop\models\ShopTax;
use skeeks\cms\shop\models\ShopVat;
use yii\grid\DataColumn;
use yii\helpers\ArrayHelper;

/**
 * Class AdminExtraController
 * @package skeeks\cms\shop\controllers
 */
class AdminAffiliateController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name                     = \skeeks\cms\shop\Module::t('app', 'Affiliates');
        $this->modelShowAttribute       = "id";
        $this->modelClassName           = ShopAffiliate::className();

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
                            'attribute' => 'site_code',
                            'class'     => SiteColumn::className(),
                        ],

                        [
                            'attribute'     => 'user_id',
                            'class'         => UserColumnData::className(),
                        ],

                        [
                            'attribute'     => 'active',
                            'class'         => BooleanColumn::className(),
                        ],

                        'plan_id',

                        [
                            'class' => CreatedAtColumn::className()
                        ]
                    ],
                ],

            ]
        );
    }

}
