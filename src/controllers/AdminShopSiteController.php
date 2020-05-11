<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\actions\BackendModelUpdateAction;
use skeeks\cms\backend\controllers\BackendModelController;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\backend\widgets\SelectModelDialogTreeWidget;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsSite;
use skeeks\cms\shop\models\ShopContent;
use skeeks\cms\shop\models\ShopSite;
use skeeks\cms\widgets\formInputs\ckeditor\Ckeditor;
use skeeks\yii2\form\fields\BoolField;
use skeeks\yii2\form\fields\WidgetField;
use yii\base\Exception;
use yii\web\Application;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminShopSiteController extends BackendModelController
{
    public function init()
    {
        $this->name = "Настройки магазина";
        $this->modelShowAttribute = false;
        $this->modelClassName = ShopSite::class;

        $this->defaultAction = "update";
        $this->generateAccessActions = false;

        /*$this->accessCallback = function () {
            if (!\Yii::$app->skeeks->site->is_default) {
                return false;
            }
            return \Yii::$app->user->can($this->uniqueId);
        };*/

        parent::init();
    }
    
    /**
     * @return Model|ActiveRecord
     */
    public function getModel()
    {
        if ($this->_model === null && \Yii::$app instanceof Application) {
            $shopSite = ShopSite::find()->where(['id' => \Yii::$app->skeeks->site->id])->one();
            if (!$shopSite) {
                $shopSite = new ShopSite();
                if (!$shopSite->save(false)) {
                    throw new Exception("!!!");
                }
            }
            $this->_model = $shopSite;
        }

        return $this->_model;
    }
    
    public function actions()
    {
        return [
            'update' => [
                'class' => BackendModelUpdateAction::class,
                'fields' => [$this, 'updateFields'],
            ],
        ];
    }

    /**
     * @return array
     */
    public function updateFields()
    {
        return [
            /*'is_supplier' => [
                'class' => BoolField::class,
                'allowNull' => false
            ],
            'is_receiver' => [
                'class' => BoolField::class,
                'allowNull' => false
            ],*/
            'catalog_cms_tree_id' => [
                'class' => WidgetField::class,
                'widgetClass' => SelectModelDialogTreeWidget::class,
            ],
            /*'description' => [
                'class' => WidgetField::class,
                'widgetClass' => Ckeditor::class
            ],
            'description_internal' => [
                'class' => WidgetField::class,
                'widgetClass' => Ckeditor::class
            ],*/
            
        ];
    }

}
