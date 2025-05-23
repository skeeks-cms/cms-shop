<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 21.09.2015
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\base\Controller;
use skeeks\cms\filters\CmsAccessControl;
use skeeks\cms\shop\models\ShopBill;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\shop\paysystem\BankTransferPaysystemHandler;
use yii\base\UserException;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopBillController extends Controller
{
    public $defaultAction = 'view';

    /**
     * @return string
     */
    public function actionView()
    {
        if (!$code = \Yii::$app->request->get('code')) {
            throw new NotFoundHttpException('');
        }

        
        if (!$bill = ShopBill::find()->where(['code' => $code])->one()) {
            throw new NotFoundHttpException('');
        }

        $this->view->title = "Счет №{$bill->id} от " . \Yii::$app->formatter->asDate($bill->created_at);

        /**
         * @var ShopBill $bill 
         */
        if ($bill->shopPaySystem && $bill->shopPaySystem->handler && $bill->shopPaySystem->handler instanceof BankTransferPaysystemHandler) {
            
            if (!$bill->receiver_contractor_bank_id) {
                if ($bill->receiverContractor) {
                    if ($bank = $bill->receiverContractor->getBanks()->one()) {
                        $bill->receiver_contractor_bank_id = $bank->id;
                        $bill->update(false, ['receiver_contractor_bank_id']);
                    }
                }
            }
            return $this->render('bank_transfer', [
                'model' => $bill,
                'isPdf' => false,
            ]);
        } else {
            return $this->render($this->action->id, [
                'model' => $bill
            ]);
        }
        
    }

    /**
     * @return string
     */
    public function actionPdf()
    {
        if (!$code = \Yii::$app->request->get('code')) {
            throw new NotFoundHttpException('');
        }


        if (!$bill = ShopBill::find()->where(['code' => $code])->one()) {
            throw new NotFoundHttpException('');
        }

        $this->view->title = $bill->asText;

        /**
         * @var ShopBill $bill
         */
        if ($bill->shopPaySystem && $bill->shopPaySystem->handler && $bill->shopPaySystem->handler instanceof BankTransferPaysystemHandler) {

            $pdf = \Yii::$app->pdf;
            $mPDF = $pdf->getApi();
            $mPDF->title = $bill->asText;
            $mPDF->subject = $bill->asText;
            //$mPDF->keywords = $bill->asText;
            $mPDF->default_font = 'helvetica';
    
            $pdf->cssFile = \Yii::getAlias('@skeeks/cms/shop/views/shop-bill/bank_transfer.css');
            $pdf->content = $this->renderPartial('@skeeks/cms/shop/views/shop-bill/bank_transfer', [
                "model"       => $bill,
                'isPdf'       => true,
                'noSignature' => \Yii::$app->request->get('noSignature'),
            ]);
    
            return $pdf->render();
            
        } else {
            
            return $this->render('view', [
                'model' => $bill
            ]);
        }

    }
    


    /**
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionGo()
    {

        if (!$code = \Yii::$app->request->get('code')) {
            throw new NotFoundHttpException('');
        }

        /**
         * @var $shopBill ShopBill
         */
        if (!$shopBill = ShopBill::find()->where(['code' => $code])->one()) {
            throw new NotFoundHttpException('');
        }

        $shopOrder = ShopOrder::find()->where(['id' => $shopBill->shop_order_id])->one();

        if ($shopOrder) {
            if ($shopOrder = ShopOrder::find()->where(['id' => $shopBill->shop_order_id])->one()) {
                throw new NotFoundHttpException('');
            }

            /**
             * @var $shopOrder ShopOrder
             */

            return $this->redirect($shopOrder->payUrl);
        } else {
            if ($shopBill->shopPaySystem && $shopBill->shopPaySystem->handler) {
                return $shopBill->shopPaySystem->handler->actionPayBill($shopBill);
            }
        }
    }
}