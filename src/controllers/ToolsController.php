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
use skeeks\cms\shop\models\ShopCmsContentElement;


/**
 * Class ToolsController
 * @package skeeks\cms\controllers
 */
class ToolsController extends Controller
{

    public $enableCsrfValidation = false;

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

    public function actionPrintPrice()
    {
        $isSpec = \Yii::$app->request->post("is-print-spec");
        $barcode = \Yii::$app->request->post("is-print-barcode");
        $ids = \Yii::$app->request->post("ids");
        $template = \Yii::$app->request->post("template");

        if (!$ids) {
            echo 'Нет товаров';
        }

        $idsArray = explode(",", $ids);
        if (!$idsArray) {
            echo 'Нет товаров';
        }

        $q = ShopCmsContentElement::find()->cmsSite()->innerJoinWith("shopProduct as sp")->andWhere(['sp.id' => $idsArray]);

        return $this->renderPartial("print-price/" . $template, [
            'q' => $q,
            'isPrintBarcode' => (bool) $barcode,
            'isPrintSpec' => (bool) $isSpec,
        ]);
    }
}
