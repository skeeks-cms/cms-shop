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

        $this->view->title = "счет №{$bill->id} от " . \Yii::$app->formatter->asDate($bill->created_at);

        return $this->render($this->action->id, [
            'model' => $bill
        ]);
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

        if (!$shopBill = ShopBill::find()->where(['code' => $code])->one()) {
            throw new NotFoundHttpException('');
        }

        if (!$shopOrder = ShopOrder::find()->where(['id' => $shopBill->shop_order_id])->one()) {
            throw new NotFoundHttpException('');
        }

        /**
         * @var $shopOrder ShopOrder
         */

        return $this->redirect($shopOrder->payUrl);
    }
}