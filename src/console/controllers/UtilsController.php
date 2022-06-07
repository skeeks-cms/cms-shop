<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\console\controllers;

use skeeks\cms\base\DynamicModel;
use skeeks\cms\models\CmsUser;
use skeeks\cms\models\CmsUserAddress;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\validators\PhoneValidator;
use yii\base\Exception;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\httpclient\Client;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class UtilsController extends Controller
{

    /**
     * @return void
     */
    public function actionOrderUpdateToUsers()
    {
        $q = ShopOrder::find()->isCreated()
            //->andWhere(['id' => '312594'])
            //->andWhere(['cms_user_id' => null])
            /*->andWhere(['not in', 'cms_site_id', [
                1
            ]])*/
        ;

        $this->stdout("Found: {$q->count()}!\n", Console::BOLD);
        if ($q->count() == 0) {
            return false;
        }
        $this->stdout("Wait: 2 sec....\n", Console::BOLD);
        sleep(2);

        /**
         * @var ShopOrder $order
         */
        foreach ($q->each() as $order) {
            $this->stdout("\t{$order->id}\n");
            $this->stdout("\t{$order->cms_site_id}\n");

            $cmsSite = $order->cmsSite;

            //В заказе указан покупатель
            if ($order->shopBuyer) {

                $shopBuyer = $order->shopBuyer;

                /**
                 * @var $cmsUser CmsUser
                 */
                $cmsUser = null;
                if ($shopBuyer->phone) {
                    $this->stdout("\tУ покупателя есть телефон: {$shopBuyer->phone}");
                    $newPhone = PhoneValidator::format($shopBuyer->phone);
                    $this->stdout(" -> {$newPhone}\n");
                    
                    //Проверка email
                    $dm = new DynamicModel(['phone']);
                    $dm->addRule("phone", PhoneValidator::class);
                    $dm->phone = $newPhone;
                    if ($dm->validate()) {
                        $order->contact_phone = $newPhone;
                        if ($cmsUser === null) {
                            $cmsUser = CmsUser::find()->cmsSite($cmsSite)->phone($newPhone)->one();
                        }
                    } else {
                        $this->stdout("\t\tТелефон некорректный!\n");
                    }
                }
                    

                if ($shopBuyer->email) {
                    $this->stdout("\tУ покупателя есть email: {$shopBuyer->email}\n");
                    $email = trim($shopBuyer->email);
                    
                    //Проверка email
                    $dm = new DynamicModel(['email']);
                    $dm->addRule("email", "email");
                    $dm->email = $email;
                    if ($dm->validate()) {
                        $order->contact_email = $email;
                        if ($cmsUser === null) {
                            $cmsUser = CmsUser::find()->cmsSite($cmsSite)->email($email)->one();
                        }
                    } else {
                        $this->stdout("\t\tEmail некорректный!\n");
                    }
                }

                if ($shopBuyer->registerName) {
                    $name = trim($shopBuyer->registerName);
                    $order->contact_first_name = $name;
                }



                $t = \Yii::$app->db->beginTransaction();

                try {

                    /**
                     * @var $cmsUser CmsUser
                     */
                    if (!$cmsUser) {
                        $this->stdout("\tСоздать пользователя\n");

                        $cmsUser = new CmsUser();
                        $cmsUser->cms_site_id = $cmsSite->id;
                        $this->stdout("\t\tСайт: {$cmsUser->cms_site_id}\n");

                        if ($order->contact_phone) {
                            $this->stdout("\t\t{$order->contact_phone}\n");
                            $cmsUser->phone = $order->contact_phone;
                        }
                        if ($order->contact_email) {
                            $this->stdout("\t\t{$order->contact_email}\n");
                            $cmsUser->email = $order->contact_email;
                        }

                        if ($shopBuyer->name) {
                            $this->stdout("\t\t{$shopBuyer->name}\n");
                            $cmsUser->first_name = $shopBuyer->name;

                        }

                        if (!$cmsUser->save()) {
                            throw new Exception("Ошибка создания пользователя: " . print_r($cmsUser->errors, true));
                        }

                        $this->stdout("\t\tПольозватель создан\n");
                    } else {
                        
                        //Можно обновить данные пользователя
                        $userUpdateData = [];
                        if (!$cmsUser->first_name && $shopBuyer->name) {
                            $cmsUser->first_name = $shopBuyer->name;
                            $userUpdateData[] = 'Имя';
                        }
                        if (!$cmsUser->email && $order->contact_email) {
                            
                            if (!CmsUser::find()->email($order->contact_email)->exists()) {
                               $cmsUser->email = $order->contact_email;
                                $userUpdateData[] = 'Email'; 
                            }
                            
                        }
                        if (!$cmsUser->phone && $order->contact_phone) {
                            
                           
                            $cmsUser->phone = $order->contact_phone;
                            $userUpdateData[] = 'Телефон';
                        }
                        
                        if ($userUpdateData) {
                            $this->stdout("\tОбновить данные пользователя {$cmsUser->id}\n");
                            if (!$cmsUser->save()) {
                                throw new Exception("Ошибка обновления пользователя: " . print_r($cmsUser->errors, true));
                            }
                        }
                    }

                    $order->cms_user_id = $cmsUser->id;


                    $address = $shopBuyer->address;

                if ($address) {
                    $decodeUrl = \Yii::$app->yaMap->createDecodeUrlByAddress($address);

                    $client = new Client();

                    $response = $client->createRequest()
                        ->setMethod('GET')
                        ->setUrl($decodeUrl)
                        ->send()
                    ;

                    if ($response->isOk) {
                        $data = ArrayHelper::getValue($response->data, 'response.GeoObjectCollection.featureMember.0');
                        $pointData = [];

                        $address = ArrayHelper::getValue($data, 'GeoObject.metaDataProperty.GeocoderMetaData.Address.formatted');
                        $point = ArrayHelper::getValue($data, 'GeoObject.Point.pos');
                        if ($point) {
                            $pointData = explode(" ", $point);
                        }

                        if ($pointData && $address) {
                            $order->delivery_longitude = ArrayHelper::getValue($pointData, "0");
                            $order->delivery_latitude = ArrayHelper::getValue($pointData, "1");
                            $order->delivery_address = $address;

                            /**
                             * @var CmsUser $cmsUser
                             */
                            if ($cmsUser) {
                                $cmsUserAddress = $cmsUser->getCmsUserAddresses()
                                    ->andWhere(['value' => $address])
                                    ->one();

                                if (!$cmsUserAddress) {
                                    $cmsUserAddress = new CmsUserAddress();

                                    $cmsUserAddress->cms_site_id = $cmsSite->id;
                                    $cmsUserAddress->cms_user_id = $cmsUser->id;
                                    $cmsUserAddress->value = $address;
                                    $cmsUserAddress->value = $address;
                                    $cmsUserAddress->latitude = $order->delivery_latitude;
                                    $cmsUserAddress->longitude = $order->delivery_longitude;

                                    if (!$cmsUserAddress->save()) {
                                        print_r($cmsUserAddress->errors);
                                        die;
                                    }
                                }

                                $order->cms_user_address_id = $cmsUserAddress->id;
                            }
                        }
                    }

                }




                    if (!$order->save()) {
                        throw new Exception("Ошибка обновления заказа: " . print_r($order->errors, true));
                    }

                    $this->stdout("\tЗаказ обновлен\n");
                    $this->stdout("----------\n");

                    $t->commit();

                } catch (\Exception $exception) {
                    $t->rollBack();
                    $this->stdout("\t\tОшибка: {$exception->getMessage()}\n");
                    sleep(2);
                }



            } else {
                $this->stdout("\t\tНет покупателя\n");
                //sleep(1);
            }

        }

    }


    
}