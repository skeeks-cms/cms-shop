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
use skeeks\cms\grid\UserColumnData;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContent;
use skeeks\cms\modules\admin\actions\modelEditor\AdminMultiModelEditAction;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\models\ShopBuyer;
use skeeks\cms\shop\models\ShopOrderStatus;
use skeeks\cms\shop\models\ShopPersonType;
use skeeks\cms\shop\models\ShopTax;
use skeeks\cms\shop\models\ShopVat;
use yii\grid\DataColumn;
use yii\helpers\ArrayHelper;

/**
 * Class AdminTaxController
 * @package skeeks\cms\shop\controllers
 */
class AdminBuyerController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name                     = skeeks\cms\shop\Module::t('app', 'Buyers');
        $this->modelShowAttribute       = "name";
        $this->modelClassName           = ShopBuyer::className();

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
                        'name',
                        [
                            'class' => UserColumnData::className(),
                            'attribute' => 'cms_user_id'
                        ],

                        [
                            'class' => DataColumn::className(),
                            'attribute' => 'shop_person_type_id',
                            'value' => function(ShopBuyer $model)
                            {
                                return $model->shopPersonType->name;
                            }
                        ]

                    ],
                ]
            ]
        );
    }

}
