<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 25.09.2015
 */
namespace skeeks\cms\shop\controllers;

use skeeks\cms\base\Controller;
use skeeks\cms\helpers\Request;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\models\forms\PasswordChangeForm;
use skeeks\cms\models\User;
use skeeks\cms\relatedProperties\models\RelatedElementModel;
use skeeks\cms\relatedProperties\models\RelatedPropertiesModel;
use skeeks\cms\shop\models\ShopPersonType;
use skeeks\modules\cms\form2\models\Form2Form;
use skeeks\modules\cms\form2\models\Form2FormSend;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Class BackendController
 * @package skeeks\cms\shop\controllers
 */
class BackendController extends Controller
{
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
     * Процесс отправки формы
     * @return array
     */
    public function actionShopPersonTypeSubmit()
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
                    $rr->message = 'Данный плательщик отключен или удален. Обновите страницу.';
                    $rr->success = false;
                    return $rr;
                }

                $modelBuyer     = $shopPersonType->createModelShopBuyer();
                $validateModel  = $modelBuyer->relatedPropertiesModel;

                if ($validateModel->load(\Yii::$app->request->post()) && $validateModel->validate())
                {
                    $modelBuyer->name = 'test';
                    $modelBuyer->cms_user_id = \Yii::$app->user->identity->id;

                    if (!$modelBuyer->save())
                    {
                        $rr->success = false;
                        $rr->message = 'Данные покупателя не сохранены';
                        return (array) $rr;
                    }

                    $validateModel->save();

                    //$rr->success = true;
                    //$rr->message = 'Успешно отправлена';

                } else
                {
                    $rr->success = false;
                    $rr->message = 'Проверьте правильность заполнения полей формы';
                }

                return (array) $rr;
            }
        }
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
                    $rr->message = 'Данный плательщик отключен или удален. Обновите страницу.';
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