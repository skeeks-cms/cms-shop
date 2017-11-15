<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 21.12.2016
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\shop\models\ShopQuantityNoticeEmail;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\Controller;

/**
 * Class NotifyController
 *
 * @package skeeks\cms\shop\controllers
 */
class NotifyController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    '*' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionAdd()
    {
        $rr = new RequestResponse();
        $model = new ShopQuantityNoticeEmail();

        if ($rr->isRequestAjaxPost()) {
            if ($model->load(\Yii::$app->request->post()) && $model->save()) {
                //Notify admins
                try {
                    if (\Yii::$app->shop->notifyEmails) {
                        foreach (\Yii::$app->shop->notifyEmails as $email) {
                            \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail';

                            \Yii::$app->mailer->compose('notice-added', [
                                'model' => $model,
                                'url' => \Yii::$app->request->referrer,
                            ])
                                ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
                                ->setTo($email)
                                ->setSubject(\Yii::$app->cms->appName . ': ' . \Yii::t('skeeks/shop/app',
                                        'Notify admission') . ' #' . $model->id)
                                ->send();
                        }
                    }
                } catch (\Exception $e) {
                    \Yii::error($e->getMessage(), static::class);
                }


                $rr->success = true;
                $rr->message = \Yii::t('skeeks/shop/app', 'Your data has been successfully added');
            } else {
                $errors = Json::encode($model->firstErrors);
                $rr->success = false;
                $rr->message = \Yii::t('skeeks/shop/app', 'Please double-check your data') . " :{$errors}";
            }
        }

        return $rr;
    }

    /**
     * @return array
     */
    public function actionAddValidate()
    {
        $rr = new RequestResponse();
        $model = new ShopQuantityNoticeEmail();

        return $rr->ajaxValidateForm($model);
    }
}
