<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 22.03.2016
 */

namespace skeeks\cms\shop\console\controllers;

use skeeks\cms\shop\models\ShopProduct;
use skeeks\cms\shop\models\ShopProductQuantityChange;
use skeeks\cms\shop\models\ShopQuantityNoticeEmail;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Class NotifyController
 *
 * @package skeeks\cms\shop\console\controllers
 */
class NotifyController extends Controller
{

    /**
     * Просмотр созданных бекапов баз данных
     */
    public function actionQuantityEmails()
    {
        $countEmails = ShopQuantityNoticeEmail::find()
            ->andWhere(['is_notified' => 0])
            ->orderBy(['created_at' => SORT_ASC])
            ->count();

        if (!$countEmails) {
            $this->stdout("Уведомить некого\n", Console::BOLD);
            return;
        }
        $this->stdout("Количество клиентов для уведомлений: ".$countEmails."\n", Console::BOLD);


        //Самый старый запрос на уведомление, начинаем искать изменения от него
        $shopQuantityNoticeEmail = ShopQuantityNoticeEmail::find()
            ->andWhere(['is_notified' => 0])
            ->orderBy(['created_at' => SORT_ASC])
            ->one();

        $this->stdout("\tСамый старый запрос на уведомление: ".\Yii::$app->formatter->asDatetime($shopQuantityNoticeEmail->created_at)."\n");

        $productIds = ShopQuantityNoticeEmail::find()
            ->andWhere(['is_notified' => 0])
            ->orderBy(['>=', 'created_at', $shopQuantityNoticeEmail->created_at])
            ->orderBy(['created_at' => SORT_ASC])
            ->groupBy('shop_product_id')
            ->indexBy('shop_product_id')
            ->asArray()
            ->all();

        if (!$productIds) {
            $this->stdout("\tТоваров не найдено\n");
        }
        $this->stdout("\tНужно проверить изменения по товарам на которые подписались: ".count($productIds)."\n");

        $productIds = array_keys($productIds);

        //Какие товары появлялись в наличии за это время?
        $productIds = ShopProductQuantityChange::find()
            ->andWhere(['shop_product_id' => $productIds])
            ->andWhere(['>=', 'created_at', $shopQuantityNoticeEmail->created_at])
            ->andWhere(['>', 'quantity', 0])
            ->groupBy('shop_product_id')
            ->indexBy('shop_product_id')
            ->asArray()
            ->all();

        if (!$productIds) {
            $this->stdout("\tНе было изменений по товарам\n");
        }
        $productIds = array_keys($productIds);
        $this->stdout("\tИзменилось товаров: ".count($productIds)."\n");
        /**
         * @var ShopProduct $product
         */
        foreach (ShopProduct::find()->where(['id' => $productIds])->each(10) as $product) {
            $this->stdout("\t\tТовар: {$product->cmsContentElement->name}\n");
            $this->stdout("\t\tНаличие: {$product->quantity}\n");

            if ($product->quantity <= 0) {
                continue;
            }

            if ($noticeEmails = $product->getShopQuantityNoticeEmails()
                ->andWhere(['is_notified' => 0])
                ->groupBy('email')
                ->orderBy(['created_at' => SORT_DESC])
                ->all()
            ) {
                $count = count($noticeEmails);
                $this->stdout("\t\tУведомить клиентов: {$count}\n");
                /**
                 * @var ShopQuantityNoticeEmail $noticeEmail
                 */
                foreach ($noticeEmails as $noticeEmail) {
                    $this->stdout("\t\t\tКлиент: {$noticeEmail->email}\n");
                    $this->_notifyCleint($noticeEmail);
                }
            }
        }
    }

    protected function _notifyCleint(ShopQuantityNoticeEmail $noticeEmail)
    {
        if ($noticeEmail->is_notified == 1) {
            $this->stdout("\t\t\tУже уведомлен\n");
            return;
        }

        /*try
        {*/
        \Yii::$app->mailer->view->theme->pathMap['@app/mail'][] = '@skeeks/cms/shop/mail';

        \Yii::$app->mailer->compose('client-quantity-notice', [
            'model' => $noticeEmail,
        ])
            ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName.''])
            ->setTo($noticeEmail->email)
            ->setSubject(\Yii::t('skeeks/shop/app',
                    "We've got the goods interesting you")." (".$noticeEmail->shopProduct->cmsContentElement->name.")")
            ->send();

        $noticeEmail->notified_at = \Yii::$app->formatter->asTimestamp(time());
        $noticeEmail->is_notified = 1;
        $noticeEmail->save();

        $this->stdout("\t\t\tУведомлен\n", Console::FG_GREEN);

        ShopQuantityNoticeEmail::updateAll([
            'is_notified' => 1,
            'notified_at' => \Yii::$app->formatter->asTimestamp(time()),
        ], [
            'shop_product_id' => $noticeEmail->shop_product_id,
            'email'           => $noticeEmail->email,
            'is_notified'     => 0,
        ]);

        /*} catch (\Exception $e)
        {
            $this->stdout("\t\t\tEmail не отправлен: {$e->getMessage()}\n", Console::FG_RED);
        }*/
    }
}