<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 14.07.2015
 */
namespace skeeks\cms\shop\actions;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\modules\admin\actions\modelEditor\AdminOneModelEditAction;
use skeeks\cms\shop\models\ShopProduct;

/**
 * Class AdminContentElementShopAction
 * @package skeeks\cms\shop\actions
 */
class AdminContentElementShopAction extends AdminOneModelEditAction
{

    public function run()
    {
        /**
         * @var $contentElement CmsContentElement
         */
        $contentElement          = $this->controller->model;
        $model          = ShopProduct::find()->where(['id' => $contentElement->id])->one();

        if (!$model)
        {
            $model = new ShopProduct([
                'id' => $contentElement->id
            ]);
        }


        $rr = new RequestResponse();

        if (\Yii::$app->request->isAjax && !\Yii::$app->request->isPjax)
        {
            return $rr->ajaxValidateForm($model);
        }

        if ($rr->isRequestPjaxPost())
        {
            if ($model->load(\Yii::$app->request->post()) && $model->save())
            {
                \Yii::$app->getSession()->setFlash('success', 'Сохранено');

                if (\Yii::$app->request->post('submit-btn') == 'apply')
                {

                } else
                {
                    return $this->controller->redirect(
                        $this->controller->indexUrl
                    );
                }

                $model->refresh();

            } else
            {
                \Yii::$app->getSession()->setFlash('error', 'Не удалось сохранить');
            }
        }

        $this->viewParams =
        [
            'model' => $model
        ];

        return parent::run();
    }

    /**
     * Renders a view
     *
     * @param string $viewName view name
     * @return string result of the rendering
     */
    protected function render($viewName)
    {
        return $this->controller->render("@skeeks/cms/shop/views/content-element/edit", (array) $this->viewParams);
    }

}
