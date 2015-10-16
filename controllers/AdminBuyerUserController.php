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
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\grid\SiteColumn;
use skeeks\cms\grid\UserColumnData;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsUser;
use skeeks\cms\modules\admin\actions\modelEditor\AdminMultiModelEditAction;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\models\ShopBuyer;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\shop\models\ShopOrderStatus;
use skeeks\cms\shop\models\ShopPersonType;
use skeeks\cms\shop\models\ShopTax;
use skeeks\cms\shop\models\ShopVat;
use yii\data\ActiveDataProvider;
use yii\grid\DataColumn;
use yii\helpers\ArrayHelper;
use yii\mongodb\file\ActiveQuery;

/**
 * Class AdminBuyerUserController
 * @package skeeks\cms\shop\controllers
 */
class AdminBuyerUserController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name                     = skeeks\cms\shop\Module::t('app', 'Buyers');
        $this->modelShowAttribute       = "name";
        $this->modelClassName           = CmsUser::className();

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
                    "dataProviderCallback" => function(ActiveDataProvider $dataProvider)
                    {
                        $query = $dataProvider->query;
                        /**
                         * @var \yii\db\ActiveQuery $query
                         */
                        //$query->select(['app_company.*', 'count(`app_company_officer_user`.`id`) as countOfficer']);
                        $query->groupBy([CmsUser::tableName() . '.id']);
                        $query->innerJoin(ShopBuyer::tableName(), '`shop_buyer`.`cms_user_id` = `cms_user`.`id`');

                    },

                    "columns"      => [
                        [
                            'class'         => UserColumnData::className(),
                            'attribute'     => 'id',
                            'label'         => skeeks\cms\shop\Module::t('app', 'Buyer')
                        ],
                        'email',
                        'phone',

                        [
                            'class'         => DateTimeColumnData::className(),
                            'attribute'     => 'created_at',
                            'label'         => skeeks\cms\shop\Module::t('app', 'Date of registration'),
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'label'         => skeeks\cms\shop\Module::t('app', 'Date of last order'),
                            'value'         => function(CmsUser $model)
                            {
                                if ($order = ShopOrder::find()->where(['user_id' => $model->id])->orderBy(['created_at' => SORT_DESC])->one())
                                {
                                    return \Yii::$app->formatter->asDatetime($order->created_at);
                                }

                                return null;
                            },
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'label'         => skeeks\cms\shop\Module::t('app', 'The amount paid orders'),
                            'value'         => function(CmsUser $model)
                            {
                                return ShopOrder::find()->where([
                                    'user_id'   => $model->id,
                                    'payed'     => Cms::BOOL_Y
                                ])->count();
                            },
                        ],


                    ],
                ]
            ]
        );
    }

}
