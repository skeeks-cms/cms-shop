<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/* @var $this yii\web\View */
/* @var $controller \skeeks\cms\backend\actions\BackendGridModelAction */
/* @var $helper \skeeks\cms\shop\helpers\ShopOfferChooseHelper */
$this->registerJs(<<<JS
$("body").on("click", ".sx-offers-add-btn", function() {
    $(this).hide();
    $(".sx-offers-add").slideDown();
    return false;
});
JS
);

$this->registerCss(<<<CSS
.sx-offer-choose {
    display: none;
}

.sx-offers-add {
    margin-top: 20px;
}

.sx-filter-offers, .sx-offers-add {
    background: #f5f9f9;
    padding: 5px 10px 5px 10px; 
    border: 1px solid #eee; 
    border-radius: 5px;
    margin-bottom: 10px;
}
.sx-filter-offers .sx-choose-property-group {
    margin-bottom: 0px;
}

.sx-offers-add {
    display: none;
}

CSS
);

?>
<? if ($createAction = \yii\helpers\ArrayHelper::getValue($controller->actions, 'create')) : ?>
    <div class="g-mb-20">
        <div class="d-flex flex-row">
            <div>
                <?
                /**
                 * @var $controller BackendModelController
                 * @var $createAction BackendModelCreateAction
                 */
                $r = new \ReflectionClass($controller->modelClassName);

                $createAction->url = \yii\helpers\ArrayHelper::merge($createAction->urlData, [
                    'parent_content_element_id' => $controller->model->id,
                ]);

                $createAction->name = "Добавить предложение";

                echo \skeeks\cms\backend\widgets\ControllerActionsWidget::widget([
                    'actions'         => [$createAction],
                    'isOpenNewWindow' => true,
                    'minViewCount'    => 1,
                    'itemTag'         => 'button',
                    'itemOptions'     => ['class' => 'btn btn-primary'],
                ]);
                ?>
            </div>
            <div class="g-ml-20">
                <a href="#" class="btn btn-secondary sx-offers-add-btn"><i class="fas fa-list"></i> Добавить несколько</a>
            </div>
        </div>

        <div class="sx-offers-add" style="">
            <h5>Пакетное добавление предложений</h5>
            <?
            $dm = new \skeeks\cms\shop\helpers\ShopCreateOffersModel([], ['shopProduct' => $controller->model->shopProduct]);

            if (\Yii::$app->request->post() && \Yii::$app->request->post($dm->formKey)) {
                ob_get_clean();
                $rr = new \skeeks\cms\helpers\RequestResponse();
                $dm->load(\Yii::$app->request->post());

                $dm->createOffers();

                $rr->message = 'Предложения добавлены';
                $rr->success = true;
                \Yii::$app->response->data = $rr;
                \Yii::$app->end();
            }

            ?>
            <? if ($dm->availableProperties) : ?>
                <? $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
                    'enableAjaxValidation' => false,
                    'fieldClass' => \skeeks\cms\backend\forms\ActiveFieldBackend::class,

                    'clientCallback' => new \yii\web\JsExpression(<<<JS
    function (ActiveFormAjaxSubmit) {
        ActiveFormAjaxSubmit.on('success', function(e, response) {
            setTimeout(function() {
                window.location.reload();
            }, 5);
        });
    }
JS
)

                ]); ?>
                <div style="display: none;">
                    <input name="<?= $dm->formKey; ?>" value="1" />
                </div>
                <? foreach ($dm->availableProperties as $property) : ?>
                    <? if ($property->property_type == \skeeks\cms\relatedProperties\PropertyType::CODE_LIST) : ?>
                        <?= $form->field($dm, $property->code)->label($property->name)->widget(\skeeks\cms\widgets\Select::class, [
                            'items'    => \yii\helpers\ArrayHelper::map($property->enums, 'id', 'value'),
                            'multiple' => true,
                        ]) ?>
                    <? elseif ($property->property_type == \skeeks\cms\relatedProperties\PropertyType::CODE_ELEMENT) : ?>
                        <?
                        $items = \skeeks\cms\models\CmsContentElement::find()->where(['content_id' => $property->handler->content_id])->all()
                        ?>
                        <?= $form->field($dm, $property->code)->label($property->name)->widget(\skeeks\cms\widgets\Select::class, [
                            'items'    => \yii\helpers\ArrayHelper::map($items, 'id', 'name'),
                            'multiple' => true,
                        ]) ?>
                    <? else : ?>

                    <? endif; ?>

                <? endforeach; ?>
                <div class="row">
                    <div class="col-md-3 text-md-right my-auto">

                    </div>
                    <div class="col-md-9">
                        <button type="submit" class="btn btn-primary">Создать предложения</button>
                    </div>

                </div>
                <? $form::end(); ?>
            <? endif; ?>

        </div>

    </div>
<? endif; ?>

<? if ($helper->availableOffers) : ?>
    <div class="sx-filter-offers" style="">
        <h5>Фильтр предложений</h5>
        <?= $helper->render(); ?>
    </div>
<? endif; ?>
