<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 21.12.2016
 */

namespace skeeks\cms\shop\widgets\notice;

use skeeks\cms\shop\models\ShopQuantityNoticeEmail;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\bootstrap\Modal;
use yii\helpers\ArrayHelper;

/**
 * Class NotifyProductEmailModalWidget
 *
 * @package skeeks\cms\shop\widgets\notice
 */
class NotifyProductEmailModalWidget extends Modal
{
    public $product_id = null;
    public $form_options = [];
    public $view_file = '';
    public $success_modal_id = '';

    /**
     * @var ActiveFormAjaxSubmit
     */
    public $form = null;

    /**
     * @var ShopQuantityNoticeEmail
     */
    public $model = null;

    public function init()
    {
        if (!$this->header) {
            $this->header = \Yii::t('skeeks/shop/app', 'Notify admission');
        }

        $this->toggleButton = ArrayHelper::merge([
            'label' => \Yii::t('skeeks/shop/app', 'Notify admission')
        ], $this->toggleButton);

        parent::init();

        $success_modal_id = $this->success_modal_id;
        $this->form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin(ArrayHelper::merge([
            'action' => \yii\helpers\Url::to('/shop/notify/add'),
            'validationUrl' => \yii\helpers\Url::to('/shop/notify/add-validate'),
            'id' => $this->id . "-form",
            'enableClientValidation' => false,
            'enableAjaxValidation' => true,
            'afterValidateCallback' => new \yii\web\JsExpression(<<<JS
            function(jForm, ajax)
            {
                var success_modal = "{$success_modal_id}";

                var handler = new sx.classes.AjaxHandlerStandartRespose(ajax, {
                    'blockerSelector' : $('#' + jForm.attr('id')).closest('.modal-body'),
                    'enableBlocker' : true,
                });

                handler.bind('success', function(e, response)
                {
                    _.delay(function()
                    {
                        $('div').modal('hide');

                        _.delay(function()
                        {
                            if (success_modal)
                            {
                                $('#' + success_modal).modal('show');
                            }
                        }, 300);

                    }, 300);
                });

                handler.bind('error', function(e, response)
                {
                    console.log(response);
                });
            }
JS
            ),
        ], $this->form_options));
    }

    /**
     * Renders the widget.
     */
    public function run()
    {
        $this->model = new ShopQuantityNoticeEmail();
        $this->model->loadDefaultValues();
        $this->model->shop_product_id = $this->product_id;

        if (!$this->model->shop_product_id) {
            throw new InvalidConfigException("Porduct id not found");
        }

        if (!$this->view_file) {
            if ($this->footer !== false) {
                $formId = $this->id . "-form";
                $this->footer = '
                    <button class="btn btn-primary" onclick="$(\'#' . $formId . '\').submit(); return false;">' . \Yii::t('skeeks/shop/app',
                        'Submit') . '</button>

                ';

                /*<button type="button" class="btn btn-default" data-dismiss="modal">' . \Yii::t('skeeks/shop/app', 'Close') . '</button>*/
            }

            echo $this->render('notify-modal-email');
        } else {
            echo $this->render($this->view_file);
        }

        \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::end();
        parent::run();
    }
}
