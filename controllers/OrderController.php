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
use skeeks\cms\filters\CmsAccessControl;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\shop\models\ShopBasket;
use skeeks\cms\shop\models\ShopBuyer;
use skeeks\cms\shop\models\ShopFuser;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\shop\models\ShopPersonType;
use skeeks\cms\shop\models\ShopPersonTypeProperty;
use skeeks\cms\shop\models\ShopProduct;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class CartController
 * @package skeeks\cms\shop\controllers
 */
class OrderController extends Controller
{
    public $defaultAction = 'view';

    /**
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [

            'accessToView' => [
                'class' => CmsAccessControl::className(),
                'only' => ['view'],
                'rules' => [
                      // deny all POST request
                      //
                    [
                        'allow' => true,
                        'matchCallback' => function($rule, $action)
                        {
                            $id = \Yii::$app->request->get('id');
                            $shopOrder = ShopOrder::findOne($id);

                            if (\Yii::$app->user->isGuest)
                            {
                                return false;
                            }

                            if ($shopOrder->user_id == \Yii::$app->user->identity->id)
                            {
                                return true;
                            }

                            return false;
                        }
                    ],
                ]
            ]
        ]);
    }

    /**
     * @return string
     */
    public function actionView()
    {
        $this->view->title = 'Заказ | Магазин';
        return $this->render($this->action->id);
    }
}