<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\console\controllers;

use ShopWbProduct;
use skeeks\cms\base\DynamicModel;
use skeeks\cms\models\CmsUser;
use skeeks\cms\models\CmsUserAddress;
use skeeks\cms\shop\models\ShopMarketplace;
use skeeks\cms\shop\models\ShopProduct;
use skeeks\cms\validators\PhoneValidator;
use yii\base\Exception;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\httpclient\Client;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class WbController extends Controller
{
    public function actionProductsUpdate()
    {
        $q = ShopMarketplace::find()->andWhere(['marketplace' => ShopMarketplace::MARKETPLACE_WILDBERRIES])->active();
        $this->stdout("Wb маркетплейсов: " . $q->count() . "\n");
        /**
         * @var $marketplace ShopMarketplace
         */
        foreach ($q->each(10) as $marketplace)
        {
            $this->stdout("Маркетплейс: " . $marketplace->name . "\n");

            if (!$marketplace->wbProvider) {
                $this->stdout("\tНе настроен!\n");
                sleep(2);
                continue;
            }

            $this->stdout("\tОбновление товаров\n");

            $productsResponse = $marketplace->wbProvider->methodContentCardsList();
            if (!$productsResponse->isOk) {
                $this->stdout("\tОшибка: {$productsResponse->error_message}\n");
            }

            $cards = ArrayHelper::getValue($productsResponse->data, 'data.cards');
            $cursor = ArrayHelper::getValue($productsResponse->data, 'data.cursor');
            $total = ArrayHelper::getValue($productsResponse->data, 'data.cursor.total');
            //todo: доработать чтобы работало если товаров более 1000
            $this->stdout("\tTotal: {$total}\n");
            if ($cards) {
                foreach ($cards as $card)
                {
                    $wb_id = (int) ArrayHelper::getValue($card, 'nmID');

                    $this->stdout("\t\twb: {$wb_id} — ");

                    $shopWbProduct = $marketplace->getShopWbProducts()->andWhere(['wb_id' => $wb_id])->one();
                    if (!$shopWbProduct) {
                        $shopWbProduct = new ShopWbProduct();
                        $shopWbProduct->wb_id = $wb_id;
                        $shopWbProduct->shop_marketplace_id = $marketplace->id;
                    }

                    $shopWbProduct->brand = (string) ArrayHelper::getValue($card, 'brand');
                    $shopWbProduct->vendor_code = (string) ArrayHelper::getValue($card, 'vendorCode');
                    $shopWbProduct->wb_object = (string) ArrayHelper::getValue($card, 'object');
                    $shopWbProduct->wb_object_id = (int) ArrayHelper::getValue($card, 'objectID');
                    $shopWbProduct->imt_id = (int) ArrayHelper::getValue($card, 'imtID');
                    $shopWbProduct->wb_updated_at_string = (string) ArrayHelper::getValue($card, 'updateAt');
                    $shopWbProduct->wb_updated_at = strtotime(ArrayHelper::getValue($card, 'updateAt'));

                    if (!$shopWbProduct->shop_product_id) {
                        //Попытка связать товар WB с товаром сайта по ID
                        $shopProduct = ShopProduct::find()->andWhere(['id' => (int) $shopWbProduct->vendor_code])->one();
                        if ($shopProduct) {
                            $shopWbProduct->shop_product_id = $shopProduct->id;
                        }
                    }

                    if (!$shopWbProduct->save()) {
                        print_r($shopWbProduct->errors);die;
                    } else {
                        $this->stdout("saved\n");
                    }
                }
            }


            $this->stdout("\tОбновление цен\n");
            sleep(2);
            $productsResponse = $marketplace->wbProvider->methodContentGetPrices();
            if (!$productsResponse->isOk) {
                $this->stdout("\tОшибка: {$productsResponse->error_message}\n");
            }

            if ($productsResponse->data) {
                foreach ($productsResponse->data as $row)
                {
                    $wb_id = (int) ArrayHelper::getValue($row, 'nmId');

                    $shopWbProduct = $marketplace->getShopWbProducts()->andWhere(['wb_id' => $wb_id])->one();
                    $this->stdout("\t\twb: {$wb_id} — ");

                    if (!$shopWbProduct) {
                        $shopWbProduct = new ShopWbProduct();
                        $shopWbProduct->wb_id = $wb_id;
                        $shopWbProduct->shop_marketplace_id = $marketplace->id;
                    }

                    $shopWbProduct->price = (float) ArrayHelper::getValue($row, 'price');
                    $shopWbProduct->discount = (float) ArrayHelper::getValue($row, 'discount');
                    $shopWbProduct->promo_code = (float) ArrayHelper::getValue($row, 'promoCode');

                    if (!$shopWbProduct->save()) {
                        print_r($shopWbProduct->errors);die;
                    } else {
                        $this->stdout("saved\n");
                    }
                }
            }
        }
    }
}