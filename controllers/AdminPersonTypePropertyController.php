<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 17.05.2015
 */
namespace skeeks\cms\shop\controllers;

use skeeks\cms\models\CmsTreeTypeProperty;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\relatedProperties\models\RelatedPropertyModel;
use skeeks\cms\shop\models\ShopPersonTypeProperty;
use yii\helpers\ArrayHelper;

/**
 * Class AdminPersonTypePropertyController
 * @package skeeks\cms\shop\controllers
 */
class AdminPersonTypePropertyController extends AdminModelEditorController
{
    public function init()
    {
        $this->name                   = skeeks\cms\shop\Module::t('app', 'Control of properties payer');
        $this->modelShowAttribute      = "name";
        $this->modelClassName          = ShopPersonTypeProperty::className();

        parent::init();

    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(),
            [
                "update" =>
                [
                    "modelScenario" => RelatedPropertyModel::SCENARIO_UPDATE_CONFIG,
                ],
            ]
        );
    }

}
