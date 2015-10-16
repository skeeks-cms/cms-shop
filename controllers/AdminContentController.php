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
class AdminContentController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name                     = skeeks\cms\shop\Module::t('app', 'Content settings');
        $this->modelShowAttribute       = "id";
        $this->modelClassName           = ShopContent::className();

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
                        [
                            'filter' => false,
                            'attribute' => 'content_id',
                            'class' => DataColumn::className(),
                            'value' => function(ShopContent $model)
                            {
                                return $model->content->name . " ({$model->content->contentType->name})";
                            }
                        ],

                        [
                            'attribute' => 'yandex_export',
                            'class' => BooleanColumn::className(),
                        ]
                    ],
                ],

            ]
        );
    }

}
