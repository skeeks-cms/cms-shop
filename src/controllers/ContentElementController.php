<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\base\Controller;
use skeeks\cms\filters\CmsAccessControl;
use skeeks\cms\helpers\StringHelper;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsTree;
use skeeks\cms\models\Tree;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopContent;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ContentElementController extends \skeeks\cms\controllers\ContentElementController
{
    public function beforeAction($action)
    {
        if ($this->model) {
            if (ShopContent::find()->where(["content_id" => $this->model->content_id])->exists()) {
                //Это магазин
                $this->model = ShopCmsContentElement::findOne($this->model->id);
                $this->model->shopProduct->createNewView();
                $this->editControllerRoute = "shop/admin-cms-content-element";
            }
        }
        return parent::beforeAction($action);
    }

}
