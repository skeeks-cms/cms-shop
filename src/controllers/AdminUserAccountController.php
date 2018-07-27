<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\grid\UserColumnData;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\models\ShopUserAccount;
use yii\grid\DataColumn;
use yii\helpers\ArrayHelper;

/**
 * Class AdminUserAccountController
 * @package skeeks\cms\shop\controllers
 */
class AdminUserAccountController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Account_customer');
        $this->modelShowAttribute = "id";
        $this->modelClassName = ShopUserAccount::className();

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
                        "columns" => [
                            'id',
                            [
                                'class'     => UserColumnData::className(),
                                'attribute' => 'user_id',
                            ],

                            [
                                'attribute' => 'current_budget',
                                'class'     => DataColumn::className(),
                                'value'     => function (ShopUserAccount $userAccount) {
                                    return (string)$userAccount->money;
                                },
                            ],

                            [
                                'attribute' => 'locked',
                                'class'     => BooleanColumn::className(),
                            ],
                        ],
                    ],

            ]
        );
    }

}
