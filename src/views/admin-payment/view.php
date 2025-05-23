<?php
/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopPayment */
/* @var $controller \skeeks\cms\backend\controllers\BackendModelController */
/* @var $action \skeeks\cms\backend\actions\BackendModelCreateAction|\skeeks\cms\backend\actions\IHasActiveForm */
$controller = $this->context;
$action = $controller->action;
$model = $action->model;
$this->render("@skeeks/cms/shop/views/admin-shop-store-doc-move/view-css");
?>

<!--<div class="row">
    <div class="col-12">
        <h5>Данные платежа</h5>
    </div>
</div>-->

<div class="sx-properties-wrapper sx-columns-1 sx-block" style="max-width: 700px;">
    <ul class="sx-properties">


        <li>
            <span class="sx-properties--name">
                Сумма
            </span>
            <span class="sx-properties--value">

                <?php
                    if ($model->is_debit) {
                        echo "<span style='color: green; font-weight: bold;'>+{$model->money}</span>";
                    } else {
                        echo "<span style='color: red; font-weight: bold;'>-{$model->money}</span>";
                    }
                ?>

            </span>
        </li>



        <li>
            <span class="sx-properties--name">
                Дата и время
            </span>
            <span class="sx-properties--value">

                <?php
                    echo \Yii::$app->formatter->asDatetime($model->created_at)
                ?>

            </span>
        </li>


        <?php if($model->shop_store_id) : ?>
            <li>
                <span class="sx-properties--name">
                    Магазин
                </span>
                <span class="sx-properties--value">
                    <?php $widget = \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                        'controllerId'            => '/shop/admin-shop-store',
                        'modelId'                 => $model->shop_store_id,
                        'isRunFirstActionOnClick' => true,
                        'options'                 => [
                            'class' => 'sx-dashed',
                            'style' => 'cursor: pointer; border-bottom: 1px dashed;',
                        ],
                    ]); ?>
                    <?php echo $model->shopStore->asText; ?>
                    <?php $widget::end(); ?>
                </span>
            </li>
        <?php endif; ?>

         <?php if($model->shop_cashebox_id) : ?>

        <?php endif; ?>

        <?php if($model->shop_cashebox_shift_id) : ?>
            <li>
                <span class="sx-properties--name">
                    Смена
                </span>
                <span class="sx-properties--value">
                    <?php $widget = \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                        'controllerId'            => '/shop/admin-shop-cashebox-shift',
                        'modelId'                 => $model->shop_cashebox_shift_id,
                        'isRunFirstActionOnClick' => true,
                        'options'                 => [
                            'class' => 'sx-dashed',
                            'style' => 'cursor: pointer; border-bottom: 1px dashed;',
                        ],
                    ]); ?>
                    <?php echo $model->shopCasheboxShift->asText; ?>
                    <?php $widget::end(); ?>
                </span>
            </li>
        <?php endif; ?>

        <?php if($model->shop_store_id) : ?>

            <li>
                <span class="sx-properties--name">
                    Оплата в магазине
                </span>
                <span class="sx-properties--value">
                    <?php echo $model->shopStorePaymentTypeAsText; ?>
                </span>
            </li>
        <?php endif; ?>

        <?php if($model->shop_check_id) : ?>
            <li>
                <span class="sx-properties--name">
                    Касса
                </span>
                <span class="sx-properties--value">
                    <?php $widget = \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                        'controllerId'            => '/shop/admin-shop-cashebox',
                        'modelId'                 => $model->shopCheck->shopCashebox->id,
                        'isRunFirstActionOnClick' => true,
                        'options'                 => [
                            'class' => 'sx-dashed',
                            'style' => 'cursor: pointer; border-bottom: 1px dashed;',
                        ],
                    ]); ?>
                    <?php echo $model->shopCheck->shopCashebox->asText; ?>
                    <?php $widget::end(); ?>
                </span>
            </li>
        <?php endif; ?>


        <?php if($model->shop_check_id) : ?>
            <li>
                <span class="sx-properties--name">
                    Чек
                </span>
                <span class="sx-properties--value">
                    <?php $widget = \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                        'controllerId'            => '/shop/admin-shop-check',
                        'modelId'                 => $model->shopCheck->id,
                        'isRunFirstActionOnClick' => true,
                        'options'                 => [
                            'class' => 'sx-dashed',
                            'style' => 'cursor: pointer; border-bottom: 1px dashed;',
                        ],
                    ]); ?>
                    <?php echo $model->shopCheck->asText; ?>
                    <?php $widget::end(); ?>
                </span>
            </li>
        <?php endif; ?>


        <?php if($model->shop_order_id) : ?>
            <li>
                <span class="sx-properties--name">
                    Заказ/возврат
                </span>
                <span class="sx-properties--value">
                    <?php $widget = \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                        'controllerId'            => '/shop/admin-order',
                        'modelId'                 => $model->shopOrder->id,
                        'isRunFirstActionOnClick' => true,
                        'options'                 => [
                            'class' => 'sx-dashed',
                            'style' => 'cursor: pointer; border-bottom: 1px dashed;',
                        ],
                    ]); ?>
                    <?php echo $model->shopOrder->asText; ?>
                    <?php $widget::end(); ?>
                </span>
            </li>
        <?php endif; ?>

        <?php if($model->cms_user_id) : ?>
            <li>
                <span class="sx-properties--name">
                    Контрагент
                </span>
                <span class="sx-properties--value">
                    <?php echo \skeeks\cms\widgets\admin\CmsUserViewWidget::widget(['cmsUser' => $model->cmsUser]); ?>
                </span>
            </li>
        <?php endif; ?>

        <?php if($model->cms_company_id) : ?>
            <li>
                <span class="sx-properties--name">
                    Компания
                </span>
                <span class="sx-properties--value">
                    <?php $widget = \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                        'controllerId'            => '/cms/admin-cms-company',
                        'modelId'                 => $model->company->id,
                        'isRunFirstActionOnClick' => true,
                        'options'                 => [
                            'class' => 'sx-dashed',
                            'style' => 'cursor: pointer; border-bottom: 1px dashed;',
                        ],
                    ]); ?>
                        <?php echo $model->company->name; ?>
                    <?php $widget::end(); ?>
                </span>
            </li>
        <?php endif; ?>


        <li>
            <span class="sx-properties--name">
                Тип платежа
            </span>
            <span class="sx-properties--value">
                <?php echo $model->shopPaySystem->name;  ?>
            </span>
        </li>

        <?php if($model->sender_contractor_id) : ?>
            <li>
                <span class="sx-properties--name">
                    Отправитель платежа
                </span>
                <span class="sx-properties--value">
                    <?php echo $model->senderContractor->asText;  ?>
                    <?php if($model->senderContractor->inn) : ?>
                        <br />ИНН: <?php echo $model->senderContractor->inn;  ?>
                    <?php endif; ?>
                    <?php if($model->sender_contractor_bank_id) : ?>
                        <br /><?php echo $model->senderContractorBank->asText;  ?>
                    <?php endif; ?>
                </span>
            </li>
        <?php endif; ?>

        <?php if($model->receiver_contractor_id) : ?>
            <li>
                <span class="sx-properties--name">
                    Получатель платежа
                </span>
                <span class="sx-properties--value">
                    <?php echo $model->receiverContractor->asText;  ?>
                    <?php if($model->receiverContractor->inn) : ?>
                        <br />ИНН: <?php echo $model->receiverContractor->inn;  ?>
                    <?php endif; ?>
                    <?php if($model->receiver_contractor_bank_id) : ?>
                        <br /><?php echo $model->receiverContractorBank->asText;  ?>
                    <?php endif; ?>
                </span>
            </li>
        <?php endif; ?>


        <li>
            <span class="sx-properties--name">
                Комментарий
            </span>
            <span class="sx-properties--value">
                <?php echo $model->comment;  ?>
            </span>
        </li>



    </ul>
</div>

