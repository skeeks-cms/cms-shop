<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\shop\models\ShopCmsContentElement;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ContentElementController extends \skeeks\cms\controllers\ContentElementController
{
    public $modelClassName = ShopCmsContentElement::class;

    public function beforeAction($action)
    {
        if ($this->model && $this->model->shopProduct) {
            //if (ShopContent::find()->where(["content_id" => $this->model->content_id])->exists()) {
            //Это магазин
            //$this->model = ShopCmsContentElement::findOne($this->model->id);
            $this->model->shopProduct->createNewView();
            $this->editControllerRoute = "shop/admin-cms-content-element";
            //}
        }
        return parent::beforeAction($action);
    }

}
