<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 21.12.2016
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\modules\admin\widgets\ActiveForm;
use skeeks\cms\shop\models\ShopFeedback;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

/**
 * Class NotifyController
 *
 * @package skeeks\cms\shop\controllers
 */
class FeedbackController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => [
                    '*' => ['post'],
                ],
            ],
        ];
    }

    public function actionCreate()
    {
        $model = new ShopFeedback();

        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {

            $t = \Yii::$app->db->beginTransaction();

            try {


                if ($model->load(\Yii::$app->request->post()) && $model->validate()) {


                    if (!$model->save()) {
                        throw new Exception("Ошибка сохранения заказа: ".print_r($order->errors, true));
                    }

                    /*$rr->data = [
                        'url' => $order->getPayUrl(),
                    ];*/

                } else {
                    $rr->data = [
                        'validation' => ArrayHelper::merge(
                            ActiveForm::validate($model),
                            []
                        ),
                    ];
                    $rr->success = false;
                    return $rr;

                }

                $t->commit();

                /*$rr->data['view_url'] = Url::to(['view', 'pk' => $model->id]);
                $rr->message = "Документ добавлен";*/
                $rr->success = true;

            } catch (\Exception $exception) {
                $t->rollBack();
                $rr->success = false;
                $rr->message = $exception->getMessage();
            }


            return $rr;
        }
    }
}
