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

use kartik\mpdf\Pdf;
use Mpdf\Mpdf;
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



        if ($isSpec) {
            $pdf = new Pdf();

            //$mpdf = $pdf->getApi();

            $format = [30, 40];
            $orientation = 'L';
            if ($template == "40x30") {
                $format = [30, 40];
                $orientation = 'L';
            } elseif($template == "30x20") {
                $format = [20, 30];
                $orientation = 'L';
            } elseif($template == "50x30") {
                $format = [30, 50];
                $orientation = 'L';
            } elseif($template == "58x30") {
                $format = [30, 58];
                $orientation = 'L';
            } elseif($template == "50x40") {
                $format = [40, 50];
                $orientation = 'L';
            }elseif($template == "58x40") {
                $format = [40, 58];
                $orientation = 'L';
            }elseif($template == "70x50") {
                $format = [50, 70];
                $orientation = 'L';
            }

            $mpdf = new \Mpdf\Mpdf([
                'orientation' => $orientation,
                'format' => $format,
                //'mode' => "c",

                'margin_left' => 0,
                'margin_right' => 0,
                'margin_top' => 0,
                'margin_bottom' => 0,
                'margin_header' => 0,
                'margin_footer' => 0,

                'default_font' => 'freesans'
            ]);

            $mpdf->use_kwt = true;
            $css = <<<CSS
* {
    font-family: "arial"
}
.label {
    line-height: 1;
}
CSS;

            $mpdf->WriteHTML($css, 1);


            $total = $q->count();
            $counter = 0;
            foreach ($q->each(10) as $element)
            {
                $counter ++;

                $content = $this->renderPartial("print-price/_" . $template, [
                    'q' => $q,
                    'isPrintBarcode' => (bool) $barcode,
                    'isPrintSpec' => false,
                    'element' =>  $element,
                ]);

                $mpdf->WriteHTML($content, 2);
                if ($counter != $total) {
                    $mpdf->AddPage();
                }

            }
            
            // return the pdf output as per the destination setting
            return $mpdf->Output();
        } else {

            $content = '';
            foreach ($q->each(10) as $element)
            {
                $content .= $this->renderPartial("print-price/_" . $template, [
                    'q' => $q,
                    'isPrintBarcode' => (bool) $barcode,
                    'isPrintSpec' => (bool) $isSpec,
                    'element' =>  $element,
                ]);
            }


            //$this->layout = "@skeeks/cms/shop/views/tools/print-price/main";
            return $this->renderPartial("print-price/main", [
                'content' => $content,
                'isPrintBarcode' => (bool) $barcode,
                'isPrintSpec' => (bool) $isSpec,
            ]);
        }
        
    }
}
