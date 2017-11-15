<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContent;
use skeeks\cms\modules\admin\actions\modelEditor\AdminMultiModelEditAction;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\models\ShopOrderStatus;
use skeeks\cms\shop\models\ShopTypePrice;
use yii\helpers\ArrayHelper;

/**
 * Class AdminTypePriceController
 * @package skeeks\cms\shop\controllers
 */
class AdminTypePriceController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Types of prices');
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopTypePrice::className();

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(),
            [
                "def-multi" =>
                    [
                        'class' => AdminMultiModelEditAction::className(),
                        "name" => \Yii::t('skeeks/shop/app', 'Default'),
                        //"icon"              => "glyphicon glyphicon-trash",
                        "eachCallback" => [$this, 'eachMultiDef'],
                        "priority" => 0,
                    ],
            ]
        );
    }

    /**
     * @param $model
     * @param $action
     * @return bool
     */
    public function eachMultiDef($model, $action)
    {
        try {
            $model->def = Cms::BOOL_Y;
            return $model->save(false);
        } catch (\Exception $e) {
            return false;
        }
    }
}
