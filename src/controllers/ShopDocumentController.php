<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 */

namespace skeeks\cms\shop\controllers;

use kartik\mpdf\Pdf;
use skeeks\cms\base\Controller;
use skeeks\cms\shop\models\ShopDocument;
use yii\web\NotFoundHttpException;

class ShopDocumentController extends Controller
{
    public $defaultAction = 'view';

    public function actionView()
    {
        $document = $this->findDocument();
        $this->view->title = $document->asText();

        return $this->render($this->viewName($document), [
            'model' => $document,
            'isPdf' => false,
        ]);
    }

    public function actionPdf()
    {
        $document = $this->findDocument();
        $this->view->title = $document->asText();

        $pdf = \Yii::$app->pdf;
        $pdf->format = Pdf::FORMAT_A4;
        $isWideDocument = in_array($document->type, [
            ShopDocument::TYPE_UPD,
            ShopDocument::TYPE_INVOICE_FACTURE,
            ShopDocument::TYPE_WAYBILL,
        ], true);

        $pdf->orientation = $isWideDocument ? Pdf::ORIENT_LANDSCAPE : Pdf::ORIENT_PORTRAIT;
        $pdf->marginLeft = $isWideDocument ? 6 : 10;
        $pdf->marginRight = $isWideDocument ? 6 : 10;
        $pdf->marginTop = $isWideDocument ? 6 : 10;
        $pdf->marginBottom = $isWideDocument ? 6 : 10;
        $pdf->marginHeader = 0;
        $pdf->marginFooter = 0;

        $mPDF = $pdf->getApi();
        $mPDF->title = $document->asText();
        $mPDF->subject = $document->asText();
        $mPDF->default_font = 'dejavusans';

        $pdf->cssFile = \Yii::getAlias('@skeeks/cms/shop/views/shop-document/document.css');
        $pdf->content = $this->renderPartial('@skeeks/cms/shop/views/shop-document/' . $this->viewName($document), [
            'model'       => $document,
            'isPdf'       => true,
            'noSignature' => \Yii::$app->request->get('noSignature'),
        ]);

        return $pdf->render();
    }

    protected function findDocument()
    {
        if (!$code = \Yii::$app->request->get('code')) {
            throw new NotFoundHttpException('');
        }

        if (!$document = ShopDocument::find()->where(['code' => $code])->one()) {
            throw new NotFoundHttpException('');
        }

        return $document;
    }

    protected function viewName(ShopDocument $document)
    {
        if ($document->type == ShopDocument::TYPE_UPD) {
            return 'upd';
        }

        if ($document->type == ShopDocument::TYPE_INVOICE_FACTURE) {
            return 'invoice-facture';
        }

        if ($document->type == ShopDocument::TYPE_WAYBILL) {
            return 'waybill';
        }

        return 'document';
    }
}
