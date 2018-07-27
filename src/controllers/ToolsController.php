<?php
/**
 * Вспомогательные иструменты
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.04.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\base\Controller;


/**
 * Class ToolsController
 * @package skeeks\cms\controllers
 */
class ToolsController extends Controller
{


    /**
     * Выбор элемента контента
     * @return string
     */
    public function actionSelectCmsElement()
    {
        $this->layout = '@skeeks/cms/modules/admin/views/layouts/main.php';
        \Yii::$app->cmsToolbar->enabled = 0;

        return $this->render($this->action->id);
    }
}
