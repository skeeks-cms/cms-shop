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
use skeeks\cms\grid\UpdatedAtColumn;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsSite;
use skeeks\cms\modules\admin\actions\modelEditor\AdminMultiModelEditAction;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\components\CartComponent;
use skeeks\cms\shop\models\ShopFuser;
use skeeks\cms\shop\models\ShopOrderStatus;
use skeeks\cms\shop\models\ShopPersonType;
use skeeks\cms\shop\models\ShopTax;
use skeeks\cms\shop\models\ShopVat;
use skeeks\cms\shop\widgets\AdminBuyerUserWidget;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\grid\DataColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Class AdminFuserController
 * @package skeeks\cms\shop\controllers
 */
class AdminFuserController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name                     = \Yii::t('skeeks/shop/app', 'Baskets');
        $this->modelShowAttribute       = "name";
        $this->modelClassName           = ShopFuser::className();

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();;

        unset($actions['create']);
        unset($actions['update']);

        return $actions;
    }

}
