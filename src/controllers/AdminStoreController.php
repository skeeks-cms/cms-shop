<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\models\CmsContentElement;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;

/**
 * Class AdminStoreController
 * @package skeeks\cms\shop\controllers
 */
class AdminStoreController extends \skeeks\cms\controllers\AdminCmsContentElementController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Stocks');
        $this->modelShowAttribute = "name";
        $this->modelClassName = CmsContentElement::class;

        parent::init();
    }
}
