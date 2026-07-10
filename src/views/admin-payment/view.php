
<?php
/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopPayment */
/* @var $controller \skeeks\cms\backend\controllers\BackendModelController */
/* @var $action \skeeks\cms\backend\actions\BackendModelCreateAction|\skeeks\cms\backend\actions\IHasActiveForm */

use skeeks\cms\backend\widgets\AjaxControllerActionsWidget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$controller = $this->context;
$action = $controller->action;
$model = $action->model;

$formatValue = static function ($value, $empty = 'Не указано') {
    $value = trim((string)$value);

    return $value === ''
        ? '<span class="sx-payment-muted">'.Html::encode($empty).'</span>'
        : Html::encode($value);
};

$entityCard = static function ($controllerId, $entity, $title, $subtitle = '', $icon = 'fa fa-file') use ($formatValue) {
    if (!$entity) {
        return '<div class="sx-payment-entity is-empty">'
            . '<div class="sx-payment-entity-icon"><i class="'.$icon.'"></i></div>'
            . '<div class="sx-payment-entity-body">'
            . '<div class="sx-payment-entity-label">'.Html::encode($title).'</div>'
            . '<div class="sx-payment-entity-title">'.$formatValue('').'</div>'
            . '</div>'
            . '</div>';
    }

    $content = '<div class="sx-payment-entity">'
        . '<div class="sx-payment-entity-icon"><i class="'.$icon.'"></i></div>'
        . '<div class="sx-payment-entity-body">'
        . '<div class="sx-payment-entity-label">'.Html::encode($title).'</div>'
        . '<div class="sx-payment-entity-title">'.Html::encode($entity->asText).'</div>';

    if ($subtitle) {
        $content .= '<div class="sx-payment-entity-subtitle">'.Html::encode($subtitle).'</div>';
    }

    $content .= '</div></div>';

    return AjaxControllerActionsWidget::widget([
        'controllerId'            => $controllerId,
        'modelId'                 => $entity->id,
        'isRunFirstActionOnClick' => true,
        'content'                 => $content,
        'options'                 => [
            'class' => 'sx-payment-entity-link',
        ],
    ]);
};

$requisiteCard = static function ($label, $value) use ($formatValue) {
    return '<div class="sx-payment-requisite">'
        . '<div class="sx-payment-requisite-label">'.Html::encode($label).'</div>'
        . '<div class="sx-payment-requisite-value">'.$formatValue($value).'</div>'
        . '</div>';
};

$sender = $model->senderContractor;
$receiver = $model->receiverContractor;
$senderBank = $model->senderContractorBank;
$receiverBank = $model->receiverContractorBank;
$paymentDirection = $model->is_debit ? 'Поступление' : 'Оплата';
$amountClass = $model->is_debit ? 'is-income' : 'is-expense';
$amountPrefix = $model->is_debit ? '+' : '-';
$externalData = is_array($model->external_data) ? $model->external_data : [];
$externalName = strtolower(trim((string)$model->external_name));
$isBankImported = in_array($externalName, ['tinkoff', 'tbank', 't-bank'], true)
    || ArrayHelper::getValue($externalData, 'operationId')
    || ArrayHelper::getValue($externalData, 'typeOfOperation');
$externalSource = [
    'tinkoff' => 'Т-Банк',
    'tbank'   => 'Т-Банк',
    't-bank'  => 'Т-Банк',
][$externalName] ?? ($model->external_name ?: 'Банк');
$externalOperationId = $model->external_id ?: ArrayHelper::getValue($externalData, 'operationId');
$externalOperationDate = ArrayHelper::getValue($externalData, 'operationDate');
$externalOperationTimestamp = $externalOperationDate ? strtotime($externalOperationDate) : false;
$externalOperationType = ArrayHelper::getValue($externalData, 'typeOfOperation');
$externalOperationType = [
    'Credit' => 'Зачисление',
    'Debit'  => 'Списание',
][$externalOperationType] ?? $externalOperationType;

$this->registerCss(<<<CSS
.sx-payment-card {
    background: #fff;
    border: 1px solid #e3e7eb;
    border-radius: 10px;
    overflow: hidden;
}
.sx-payment-section {
    padding: 22px 28px;
    border-bottom: 1px solid #edf0f2;
}
.sx-payment-section:last-child {
    border-bottom: 0;
}
.sx-payment-section-title {
    margin: 0 0 14px;
    font-size: 18px;
    font-weight: 600;
}
.sx-payment-overview {
    display: grid;
    grid-template-columns: minmax(220px, 1.35fr) repeat(3, minmax(0, 1fr));
    gap: 12px;
}
.sx-payment-overview-item {
    min-height: 82px;
    padding: 14px;
    border: 1px solid #e3e7eb;
    border-radius: 8px;
    background: #fff;
}
.sx-payment-overview-label,
.sx-payment-entity-label,
.sx-payment-requisite-label {
    color: #8a929a;
    font-size: 12px;
    margin-bottom: 4px;
}
.sx-payment-overview-value {
    color: #303942;
    font-weight: 600;
    overflow-wrap: anywhere;
}
.sx-payment-amount {
    font-size: 24px;
    line-height: 1.25;
}
.sx-payment-amount.is-income {
    color: #188b38;
}
.sx-payment-amount.is-expense {
    color: #c43c35;
}
.sx-payment-entities {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}
.sx-payment-entity,
.sx-payment-entity-link {
    box-sizing: border-box;
    display: block;
    color: inherit;
    text-decoration: none;
}
.sx-payment-entity-link {
    height: 100%;
    cursor: pointer;
}
.sx-payment-entity {
    min-height: 84px;
    height: 100%;
    padding: 14px;
    border: 1px solid #e3e7eb;
    border-radius: 8px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    background: #fff;
    transition: border-color .15s ease, box-shadow .15s ease;
}
.sx-payment-entity-link:hover,
.sx-payment-entity-link:focus {
    color: inherit;
    text-decoration: none;
    outline: none;
}
.sx-payment-entity-link:hover .sx-payment-entity,
.sx-payment-entity-link:focus .sx-payment-entity {
    border-color: #9dc8f0;
    box-shadow: 0 8px 24px rgba(31, 82, 130, .08);
}
.sx-payment-entity-icon {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #eef3f7;
    color: #607080;
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    font-size: 15px;
}
.sx-payment-entity-title,
.sx-payment-requisite-value {
    font-weight: 600;
    overflow-wrap: anywhere;
}
.sx-payment-entity-subtitle {
    color: #606a73;
    font-size: 13px;
    margin-top: 4px;
}
.sx-payment-muted {
    color: #a5adb5;
}
.sx-payment-bank-columns {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 24px;
}
.sx-payment-bank-title {
    margin: 0 0 12px;
    color: #606a73;
    font-size: 14px;
    font-weight: 600;
}
.sx-payment-requisites {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}
.sx-payment-requisite {
    min-height: 72px;
    padding: 12px;
    border-radius: 8px;
    background: #f8fafb;
}
.sx-payment-comment {
    margin: 0;
    color: #4d5963;
    white-space: pre-wrap;
}
.sx-payment-import {
    display: flex;
    align-items: flex-start;
    gap: 14px;
}
.sx-payment-import-icon {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #eaf6ee;
    color: #188b38;
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
}
.sx-payment-import-title {
    color: #303942;
    font-weight: 600;
}
.sx-payment-import-description {
    margin-top: 3px;
    color: #77818a;
    font-size: 13px;
}
.sx-payment-import-details {
    display: flex;
    flex-wrap: wrap;
    gap: 8px 24px;
    margin-top: 10px;
    color: #606a73;
    font-size: 13px;
}
.sx-payment-import-detail strong {
    color: #303942;
    font-weight: 500;
}
@media (max-width: 1100px) {
    .sx-payment-overview {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media (max-width: 900px) {
    .sx-payment-overview,
    .sx-payment-entities,
    .sx-payment-bank-columns,
    .sx-payment-requisites {
        grid-template-columns: 1fr;
    }
}
CSS
);
?>

<div class="sx-payment-card">
    <section class="sx-payment-section">
        <div class="sx-payment-overview">
            <div class="sx-payment-overview-item">
                <div class="sx-payment-overview-label"><?= Html::encode($paymentDirection); ?></div>
                <div class="sx-payment-overview-value sx-payment-amount <?= $amountClass; ?>">
                    <?= Html::encode($amountPrefix.(string)$model->money); ?>
                </div>
            </div>
            <div class="sx-payment-overview-item">
                <div class="sx-payment-overview-label">Дата и время</div>
                <div class="sx-payment-overview-value"><?= Html::encode(Yii::$app->formatter->asDatetime($model->created_at)); ?></div>
            </div>
            <div class="sx-payment-overview-item">
                <div class="sx-payment-overview-label">Тип платежа</div>
                <div class="sx-payment-overview-value"><?= $formatValue($model->shopPaySystem ? $model->shopPaySystem->name : ''); ?></div>
            </div>
            <div class="sx-payment-overview-item">
                <div class="sx-payment-overview-label">Компания</div>
                <div class="sx-payment-overview-value"><?= $formatValue($model->company ? $model->company->name : ''); ?></div>
            </div>
        </div>
    </section>

    <section class="sx-payment-section">
        <h3 class="sx-payment-section-title">Стороны платежа</h3>
        <div class="sx-payment-entities">
            <?= $entityCard('/cms/admin-cms-contractor', $sender, 'Отправитель', $sender && $sender->inn ? 'ИНН '.$sender->inn : '', 'fa fa-arrow-up'); ?>
            <?= $entityCard('/cms/admin-cms-contractor', $receiver, 'Получатель', $receiver && $receiver->inn ? 'ИНН '.$receiver->inn : '', 'fa fa-arrow-down'); ?>
        </div>
    </section>

    <?php if ($senderBank || $receiverBank) : ?>
        <section class="sx-payment-section">
            <h3 class="sx-payment-section-title">Банковские реквизиты</h3>
            <div class="sx-payment-bank-columns">
                <?php if ($senderBank) : ?>
                    <div>
                        <h4 class="sx-payment-bank-title">Банк отправителя</h4>
                        <div class="sx-payment-requisites">
                            <?= $requisiteCard('Банк', $senderBank->bank_name); ?>
                            <?= $requisiteCard('БИК', $senderBank->bic); ?>
                            <?= $requisiteCard('Корр. счет', $senderBank->correspondent_account); ?>
                            <?= $requisiteCard('Расчетный счет', $senderBank->checking_account); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($receiverBank) : ?>
                    <div>
                        <h4 class="sx-payment-bank-title">Банк получателя</h4>
                        <div class="sx-payment-requisites">
                            <?= $requisiteCard('Банк', $receiverBank->bank_name); ?>
                            <?= $requisiteCard('БИК', $receiverBank->bic); ?>
                            <?= $requisiteCard('Корр. счет', $receiverBank->correspondent_account); ?>
                            <?= $requisiteCard('Расчетный счет', $receiverBank->checking_account); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if (trim((string)$model->comment) !== '') : ?>
        <section class="sx-payment-section">
            <h3 class="sx-payment-section-title">Комментарий</h3>
            <p class="sx-payment-comment"><?= Html::encode($model->comment); ?></p>
        </section>
    <?php endif; ?>

    <?php if ($model->company || $model->bills || $model->deals || $model->shopOrder || $model->shopCheck || $model->shopStore) : ?>
        <section class="sx-payment-section">
            <h3 class="sx-payment-section-title">Связи</h3>
            <div class="sx-payment-entities">
                <?php if ($model->company) : ?>
                    <?= $entityCard('/cms/admin-cms-company', $model->company, 'Компания', '', 'fa fa-building'); ?>
                <?php endif; ?>
                <?php foreach ($model->bills as $bill) : ?>
                    <?= $entityCard('/cms/admin-cms-bill', $bill, 'Счет', '', 'fa fa-file'); ?>
                <?php endforeach; ?>
                <?php foreach ($model->deals as $deal) : ?>
                    <?= $entityCard('/cms/admin-cms-deal', $deal, 'Сделка', '', 'fa fa-file'); ?>
                <?php endforeach; ?>
                <?php if ($model->shopOrder) : ?>
                    <?= $entityCard('/shop/admin-order', $model->shopOrder, 'Заказ', '', 'fa fa-shopping-cart'); ?>
                <?php endif; ?>
                <?php if ($model->shopCheck) : ?>
                    <?= $entityCard('/shop/admin-shop-check', $model->shopCheck, 'Чек', '', 'fa fa-receipt'); ?>
                <?php endif; ?>
                <?php if ($model->shopStore) : ?>
                    <?= $entityCard('/shop/admin-shop-store', $model->shopStore, 'Магазин', '', 'fa fa-store'); ?>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($isBankImported) : ?>
        <section class="sx-payment-section">
            <div class="sx-payment-import">
                <div class="sx-payment-import-icon"><i class="fas fa-university"></i></div>
                <div>
                    <div class="sx-payment-import-title">Платеж загружен из банка автоматически</div>
                    <div class="sx-payment-import-description">
                        Данные получены из банковской выписки и сохранены вместе с платежом.
                    </div>
                    <div class="sx-payment-import-details">
                        <span class="sx-payment-import-detail">Источник: <strong><?= Html::encode($externalSource); ?></strong></span>
                        <?php if ($externalOperationId) : ?>
                            <span class="sx-payment-import-detail">ID операции: <strong><?= Html::encode($externalOperationId); ?></strong></span>
                        <?php endif; ?>
                        <?php if ($externalOperationTimestamp) : ?>
                            <span class="sx-payment-import-detail">Дата операции: <strong><?= Html::encode(Yii::$app->formatter->asDatetime($externalOperationTimestamp)); ?></strong></span>
                        <?php endif; ?>
                        <?php if ($externalOperationType) : ?>
                            <span class="sx-payment-import-detail">Операция: <strong><?= Html::encode($externalOperationType); ?></strong></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>
</div>
