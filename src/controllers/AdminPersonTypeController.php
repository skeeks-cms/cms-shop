<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\shop\models\ShopPersonType;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class AdminPersonTypeController extends BackendModelStandartController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Types of payers');
        $this->modelShowAttribute = "name";
        $this->modelClassName = ShopPersonType::class;

        /*$this->generateAccessActions = false;

        $this->accessCallback = function () {
            if (!\Yii::$app->cms->site->is_default) {
                return false;
            }
            return \Yii::$app->user->can($this->uniqueId);
        };*/
        
        parent::init();
    }
}
