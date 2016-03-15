<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 21.09.2015
 */
namespace skeeks\cms\shop\controllers;

use skeeks\cms\base\Controller;
use skeeks\cms\components\Cms;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\models\forms\SignupForm;
use skeeks\cms\shop\models\ShopBasket;
use skeeks\cms\shop\models\ShopBuyer;
use skeeks\cms\shop\models\ShopFuser;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\shop\models\ShopPersonType;
use skeeks\cms\shop\models\ShopPersonTypeProperty;
use skeeks\cms\shop\models\ShopProduct;
use yii\base\Exception;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\validators\EmailValidator;

/**
 * Class CartController
 * @package skeeks\cms\shop\controllers
 */
class CartController extends Controller
{
    public $defaultAction = 'cart';

    /**
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [

            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'shop-person-type-validate'  => ['post'],
                    'shop-person-type-submit'    => ['post'],
                ],
            ],
        ]);
    }


    /**
     * @return string
     */
    public function actionCart()
    {
        $this->view->title = \skeeks\cms\shop\Module::t('app', 'Basket').' | '.\skeeks\cms\shop\Module::t('app', 'Shop');
        return $this->render($this->action->id);
    }

    /**
     * @return string
     */
    public function actionCheckout()
    {
        $this->view->title = \skeeks\cms\shop\Module::t('app', 'Checkout').' | '.\skeeks\cms\shop\Module::t('app', 'Shop');
        return $this->render($this->action->id);
    }
    /**
     * @return string
     */
    public function actionPayment()
    {
        $this->view->title = \skeeks\cms\shop\Module::t('app', 'Choose payment method').' | '.\skeeks\cms\shop\Module::t('app', 'Shop');
        return $this->render($this->action->id);
    }





    /**
     * Добавление продукта в корзину.
     *
     * @return array|\yii\web\Response
     */
    public function actionAddProduct()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost())
        {
            $product_id         = \Yii::$app->request->post('product_id');
            $quantity           = \Yii::$app->request->post('quantity');

            /**
             * @var ShopProduct $product
             */
            $product = ShopProduct::find()->where(['id' => $product_id])->one();

            if (!$product)
            {
                $rr->message = \skeeks\cms\shop\Module::t('app', 'This product is not found, it may be removed.');
                return (array) $rr;
            }

            if ($product->measure_ratio > 1)
            {
                if ( $quantity % $product->measure_ratio != 0)
                {
                    $quantity = $product->measure_ratio;
                }
            }

            $shopBasket = ShopBasket::find()->where([
                'fuser_id'      => \Yii::$app->shop->shopFuser->id,
                'product_id'    => $product_id,
                'order_id'      => null,
            ])->one();

            if (!$shopBasket)
            {
                $shopBasket = new ShopBasket([
                    'fuser_id'          => \Yii::$app->shop->shopFuser->id,
                    'product_id'        => $product->id,
                    'quantity'          => 0,
                ]);
            }

            $shopBasket->quantity                   = $shopBasket->quantity + $quantity;


            if (!$shopBasket->recalculate()->save())
            {
                $rr->success = false;
                $rr->message = \skeeks\cms\shop\Module::t('app', 'Failed to add item to cart');
            } else
            {
                $shopBasket->recalculate()->save();

                $rr->success = true;
                $rr->message = \skeeks\cms\shop\Module::t('app', 'Item added to cart');
            }

            \Yii::$app->shop->shopFuser->link('site', \Yii::$app->cms->site);
            $rr->data = \Yii::$app->shop->shopFuser->toArray([], \Yii::$app->shop->shopFuser->extraFields());
            return (array) $rr;
        } else
        {
            return $this->goBack();
        }
    }


    public function actionRemoveBasket()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost())
        {
            $basket_id = \Yii::$app->request->post('basket_id');

            $shopBasket = ShopBasket::find()->where(['id' => $basket_id ])->one();
            if ($shopBasket)
            {
                if ($shopBasket->delete())
                {
                    $rr->success = true;
                    $rr->message = \skeeks\cms\shop\Module::t('app', 'Position successfully removed');
                }
            }

            \Yii::$app->shop->shopFuser->link('site', \Yii::$app->cms->site);
            $rr->data = \Yii::$app->shop->shopFuser->toArray([], \Yii::$app->shop->shopFuser->extraFields());
            return (array) $rr;
        } else
        {
            return $this->goBack();
        }
    }


    public function actionClear()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost())
        {
            foreach (\Yii::$app->shop->shopFuser->shopBaskets as $basket)
            {
                $basket->delete();
            }

            \Yii::$app->shop->shopFuser->link('site', \Yii::$app->cms->site);
            $rr->data = \Yii::$app->shop->shopFuser->toArray([], \Yii::$app->shop->shopFuser->extraFields());
            $rr->success = true;
            $rr->message = "";

            return (array) $rr;
        } else
        {
            return $this->goBack();
        }
    }

    public function actionUpdateBasket()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost())
        {
            $basket_id  = (int) \Yii::$app->request->post('basket_id');
            $quantity   = (float) \Yii::$app->request->post('quantity');

            /**
             * @var $shopBasket ShopBasket
             */
            $shopBasket = ShopBasket::find()->where(['id' => $basket_id ])->one();
            if ($shopBasket)
            {
                if ($quantity > 0)
                {
                    $product = $shopBasket->product;

                    if ($product->measure_ratio > 1)
                    {
                        if ( $quantity % $product->measure_ratio != 0)
                        {
                            $quantity = $product->measure_ratio;
                        }
                    }

                    $shopBasket->quantity = $quantity;
                    if ($shopBasket->recalculate()->save())
                    {
                        $rr->success = true;
                        $rr->message = \skeeks\cms\shop\Module::t('app', 'Postion successfully updated');
                    }

                } else
                {
                    if ($shopBasket->delete())
                    {
                        $rr->success = true;
                        $rr->message = \skeeks\cms\shop\Module::t('app', 'Position successfully removed');
                    }
                }

            }

            \Yii::$app->shop->shopFuser->link('site', \Yii::$app->cms->site);
            $rr->data = \Yii::$app->shop->shopFuser->toArray([], \Yii::$app->shop->shopFuser->extraFields());
            return (array) $rr;
        } else
        {
            return $this->goBack();
        }
    }


    public function actionUpdateBuyer()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost())
        {
            $buyerId  = \Yii::$app->request->post('buyer');
            $buyer = null;

            if (strpos($buyerId, '-') === false)
            {
                /**
                 * @var $buyer ShopBuyer
                 * @var $shopPersonType ShopPersonType
                 */
                $buyer = ShopBuyer::findOne($buyerId);
            } else
            {
                $shopPersonTypeId = explode("-", $buyerId);
                $shopPersonTypeId = $shopPersonTypeId[1];

                $shopPersonType = ShopPersonType::findOne($shopPersonTypeId);

            }

            if ($buyer)
            {
                \Yii::$app->shop->shopFuser->buyer_id = $buyer->id;
                \Yii::$app->shop->shopFuser->person_type_id = $buyer->shopPersonType->id;
            } else if ($shopPersonType)
            {
                \Yii::$app->shop->shopFuser->person_type_id = $shopPersonType->id;
                \Yii::$app->shop->shopFuser->buyer_id = null;
            }

            \Yii::$app->shop->shopFuser->save();
            \Yii::$app->shop->shopFuser->link('site', \Yii::$app->cms->site);

            $rr->message = "";
            $rr->success = true;


            $rr->data = \Yii::$app->shop->shopFuser->toArray([], \Yii::$app->shop->shopFuser->extraFields());
            return (array) $rr;
        } else
        {
            return $this->goBack();
        }
    }



    /**
     * Создание заказа
     * @return array|\yii\web\Response
     */
    public function actionCreateOrder()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost())
        {
            try
            {
                $fuser = \Yii::$app->shop->shopFuser;

                if (!$fuser->shopBaskets)
                {
                    throw new Exception(\skeeks\cms\shop\Module::t('app', 'Your basket is empty'));
                }

                if ($fuser->load(\Yii::$app->request->post()) && $fuser->save())
                {
                    $rr->success = true;
                    $rr->message = "";

                    $fuser->scenario = ShopFuser::SCENARIO_CREATE_ORDER;

                    if ($fuser->validate())
                    {
                        $order = ShopOrder::createOrderByFuser($fuser);

                        if (!$order->isNewRecord)
                        {
                            $rr->message = \skeeks\cms\shop\Module::t('app', 'The order #{order_id} created successfully', ['order_id' => $order->id]);
                            $rr->success = true;
                            $rr->redirect = Url::to(['/shop/order/view', 'id' => $order->id]);
                            $rr->data = [
                                'order' => $order
                            ];





                        } else
                        {
                            throw new Exception(\skeeks\cms\shop\Module::t('app', 'Incorrect data of the new order').": " . array_shift($order->getFirstErrors()));
                        }

                    } else
                    {
                        throw new Exception(\skeeks\cms\shop\Module::t('app', 'Not enogh data for ordering').": " . array_shift($fuser->getFirstErrors()));
                    }

                } else
                {
                    throw new Exception(\skeeks\cms\shop\Module::t('app', 'Not enogh data for ordering').": " . array_shift($fuser->getFirstErrors()));
                }

            } catch (Exception $e)
            {
                $rr->message = $e->getMessage();
                $rr->success = false;
            }


            $rr->data = \Yii::$app->shop->shopFuser->toArray([], \Yii::$app->shop->shopFuser->extraFields());
            return (array) $rr;
        } else
        {
            return $this->goBack();
        }
    }





    /**
     * Процесс отправки формы
     * @return array
     */
    public function actionShopPersonTypeSubmit()
    {
        $rr = new RequestResponse();

        try
        {
            if (\Yii::$app->request->isAjax && !\Yii::$app->request->isPjax)
            {
                if (\Yii::$app->request->post('shop_person_type_id'))
                {
                    $shop_person_type_id    = \Yii::$app->request->post('shop_person_type_id');
                    $shop_buyer_id          = \Yii::$app->request->post('shop_buyer_id');

                    /**
                     * @var $shopPersonType ShopPersonType
                     */
                    $modelBuyer = ShopBuyer::findOne($shop_buyer_id);
                    $shopPersonType = ShopPersonType::find()->active()->andWhere(['id' => $shop_person_type_id])->one();
                    if (!$shopPersonType)
                    {
                        throw new Exception(\skeeks\cms\shop\Module::t('app', 'This payer is disabled or deleted. Refresh the page.'));
                    }

                    if (!$modelBuyer)
                    {
                        $modelBuyer     = $shopPersonType->createModelShopBuyer();
                    }

                    $validateModel  = $modelBuyer->relatedPropertiesModel;

                    if ($validateModel->load(\Yii::$app->request->post()) && $validateModel->validate())
                    {
                        $modelBuyerName = [];

                        //Проверка свойств
                        foreach ($validateModel->toArray($validateModel->attributes()) as $code => $value)
                        {
                            /**
                             * @var $property ShopPersonTypeProperty
                             */
                            $property = $validateModel->getRelatedProperty($code);
                            if ($property->is_buyer_name == Cms::BOOL_Y)
                            {
                                $modelBuyerName[] = $value;
                            }

                            if ($property->is_user_email == Cms::BOOL_Y)
                            {
                                $userEmail = $value;
                            }

                            if ($property->is_user_name == Cms::BOOL_Y)
                            {
                                $userName = $value;
                            }

                            if ($property->is_user_username == Cms::BOOL_Y)
                            {
                                $userUsername = $value;
                            }

                            if ($property->is_user_phone == Cms::BOOL_Y)
                            {
                                $userPhone = $value;
                            }
                        }

                        //Нужно создать польозвателя
                        if (\Yii::$app->user->isGuest)
                        {

                            if (!$userEmail)
                            {
                                throw new Exception(\skeeks\cms\shop\Module::t('app', 'Unknown email address user'));
                            }


                            $newUser             = new SignupForm();
                            $newUser->scenario   = SignupForm::SCENARION_ONLYEMAIL;
                            $newUser->email      = $userEmail;

                            if (!$user = $newUser->signup())
                            {
                                throw new Exception(\skeeks\cms\shop\Module::t('app', 'Do not create a user profile.'));
                            }

                            if ($userName)
                            {
                                $user->name      = $userName;
                            }

                            //Авторизация пользователя
                            \Yii::$app->user->login($user, 0);

                        }


                        $modelBuyer->name                   = $modelBuyerName ? implode(", ", $modelBuyerName) : $shopPersonType->name . " от (" . \Yii::$app->formatter->asDate(time(), 'medium') . ")";
                        $modelBuyer->cms_user_id            = \Yii::$app->user->identity->id;
                        $modelBuyer->shop_person_type_id    = $shopPersonType->id;

                        if (!$modelBuyer->save())
                        {
                            throw new Exception(\skeeks\cms\shop\Module::t('app', 'The data for the buyer are not saved.'));
                        }

                        $validateModel->save();

                        \Yii::$app->shop->shopFuser->buyer_id           = $modelBuyer->id;
                        \Yii::$app->shop->shopFuser->person_type_id     = $modelBuyer->shopPersonType->id;

                        \Yii::$app->shop->shopFuser->save();

                        $rr->success = true;
                        $rr->message = \skeeks\cms\shop\Module::t('app', 'Successfully sent');

                    } else
                    {
                        throw new Exception(\skeeks\cms\shop\Module::t('app', 'Check the correctness of filling the form fields'));
                    }


                }
            }
        } catch (\Exception $e)
        {
            $rr->success = false;
            $rr->message = $e->getMessage();
        }

        return (array) $rr;
    }

    /**
     * Валидация данных с формы
     * @return array
     */
    public function actionShopPersonTypeValidate()
    {
        $rr = new RequestResponse();

        if (\Yii::$app->request->isAjax && !\Yii::$app->request->isPjax)
        {
            if (\Yii::$app->request->post('shop_person_type_id'))
            {
                $shop_person_type_id = \Yii::$app->request->post('shop_person_type_id');

                /**
                 * @var $shopPersonType ShopPersonType
                 */
                $shopPersonType = ShopPersonType::find()->active()->andWhere(['id' => $shop_person_type_id])->one();
                if (!$shopPersonType)
                {
                    $rr->message = \skeeks\cms\shop\Module::t('app', 'This payer is disabled or deleted. Refresh the page.');
                    $rr->success = false;
                    return $rr;
                }

                $modelHasRelatedProperties = $shopPersonType->createModelShopBuyer();

                if (method_exists($modelHasRelatedProperties, "createPropertiesValidateModel"))
                {
                    $model = $modelHasRelatedProperties->createPropertiesValidateModel();
                } else
                {
                    $model = $modelHasRelatedProperties->getRelatedPropertiesModel();
                }

                return $rr->ajaxValidateForm($model);
            }
        }
    }
}