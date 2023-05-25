<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\controllers;

use skeeks\cms\backend\BackendController;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\grid\DateTimeColumnData;
use skeeks\cms\shop\models\ShopBuyer;
use skeeks\cms\shop\models\ShopOrder;
use yii\base\Event;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\UnsetArrayValue;
use yii\web\NotFoundHttpException;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class UpaOrderController extends BackendController
{
    public function init()
    {
        $this->name = \Yii::t('skeeks/shop/app', 'Orders');
        $this->generateAccessActions = false;
        /*$this->permissionNames = [
            "shop/upa-order" => 'Доступ к персональной части',
        ];*/
        parent::init();
    }

    public function actionIndex()
    {
        return $this->render($this->action->id);
    }
    
    public function actionView()
    {
        $pk = \Yii::$app->request->get("pk");
        if (!$pk) {
            throw new NotFoundHttpException("Заказ не найден!");
        }
        $order = ShopOrder::find()->andWhere(['id' => (int) $pk])->andWhere(['cms_user_id' => \Yii::$app->user->id])->one();
        if (!$pk) {
            throw new NotFoundHttpException("Заказ не найден!");
        }
        return $this->render($this->action->id, [
            'model' => $order
        ]);
    }
}