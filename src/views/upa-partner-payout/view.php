<?php
/**
 * Карточка заявки на вывод бонусов.
 *
 * @var $this yii\web\View
 * @var $model \skeeks\cms\shop\models\ShopPartnerPayout
 */

use skeeks\cms\shop\models\ShopPartnerPayout;
use skeeks\cms\backend\widgets\BackendSurfaceWidget;
use skeeks\cms\widgets\admin\CmsCommentWidget;
use skeeks\cms\widgets\admin\CmsLogListWidget;
use skeeks\cms\widgets\Pjax;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$isActionWindow = (bool)ArrayHelper::getValue((array)\Yii::$app->request->get('_sxb'), 'el');

$this->title = 'Вывод бонусов №'.(int)$model->id;

$this->registerCss(<<<CSS
body.sx-empty .sx-content-wrapper {
    padding-top: 16px;
}
.sx-partner-payout-header {
    align-items: flex-start;
    display: flex;
    gap: 1rem;
    justify-content: space-between;
    margin-bottom: 1rem;
}
.sx-partner-payout-header__title {
    margin: 0 0 0.5rem;
}
.sx-partner-payout-requisites {
    white-space: pre-line;
}
.sx-partner-payout-note {
    margin-top: 1rem;
}
CSS
);
?>

<div class="sx-partner-payout-header">
    <div>
        <h1 class="sx-partner-payout-header__title">Вывод бонусов №<?= (int)$model->id; ?></h1>
        <span class="<?= ShopPartnerPayout::statusCssClass($model->status); ?>">
            <?= Html::encode($model->statusName); ?>
        </span>
    </div>
    <?php if (!$isActionWindow) : ?>
        <div>
            <?= Html::a('Назад', ['index'], [
                'class' => 'btn btn-default sx-button sx-button--secondary',
                'data-pjax' => '0',
            ]); ?>
        </div>
    <?php endif; ?>
</div>

<div class="sx-detail-layout">
    <aside class="sx-detail-layout__aside sx-surface-stack">
        <?php BackendSurfaceWidget::begin(['title' => 'Данные заявки', 'titleTag' => 'h2', 'raised' => true, 'responsive' => true]); ?>
            <table class="sx-key-value-view">
                <tbody>
                    <tr>
                        <th>Сумма</th>
                        <td>
                            <strong><?= \Yii::$app->formatter->asDecimal((float)$model->value, 2); ?> бонусов</strong>
                            = <?= \Yii::$app->formatter->asDecimal((float)$model->value, 2); ?> руб.
                        </td>
                    </tr>
                    <tr>
                        <th>Создана</th>
                        <td><?= $model->created_at ? \Yii::$app->formatter->asDatetime($model->created_at) : '—'; ?></td>
                    </tr>
                    <tr>
                        <th>Реквизиты для выплаты</th>
                        <td><div class="sx-partner-payout-requisites"><?= Html::encode((string)$model->requisites); ?></div></td>
                    </tr>
                    <?php if ($model->paid_at) : ?>
                        <tr>
                            <th>Выплачена</th>
                            <td><?= \Yii::$app->formatter->asDatetime($model->paid_at); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if ($model->status === ShopPartnerPayout::STATUS_REJECTED && $model->reject_reason) : ?>
                        <tr>
                            <th>Причина отмены</th>
                            <td><?= nl2br(Html::encode($model->reject_reason)); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if ($model->status === ShopPartnerPayout::STATUS_PAID && $model->manager_comment) : ?>
                        <tr>
                            <th>Сообщение о выплате</th>
                            <td><?= nl2br(Html::encode($model->manager_comment)); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php BackendSurfaceWidget::end(); ?>

        <?php if ($model->status === ShopPartnerPayout::STATUS_PAID) : ?>
            <div class="alert alert-success">
                Выплата отправлена, бонусы списаны с баланса.
                <?= Html::a('Движение бонусов', ['/shop/upa-partner-bonus'], ['data-pjax' => '0']); ?>
            </div>
        <?php elseif ($model->status === ShopPartnerPayout::STATUS_REJECTED) : ?>
            <div class="alert alert-warning">
                Заявка отменена, бонусы остались на вашем балансе — их можно вывести повторно
                или направить на оплату наших услуг.
            </div>
        <?php else : ?>
            <div class="alert alert-info">
                Заявка в обработке. Указанная сумма зарезервирована и станет доступна снова,
                если заявку придётся отменить.
            </div>
        <?php endif; ?>
    </aside>

    <main class="sx-detail-layout__main">
        <?php $commentsPjax = Pjax::begin(['id' => 'partner-payout-client-comments', 'options' => ['class' => 'sx-surface-stack sx-activity-thread']]); ?>
            <?php BackendSurfaceWidget::begin([
                'title' => 'Написать менеджеру',
                'hint' => 'Задайте вопрос или уточните информацию по этой выплате.',
                'titleTag' => 'h2',
                'raised' => true,
                'responsive' => true,
            ]); ?>
                <?= CmsCommentWidget::widget([
                    'model' => $model,
                    'backend_url' => ['/shop/upa-partner-payout/add-comment', 'pk' => $model->id],
                    'isShowAttachments' => false,
                    'isShowPin' => false,
                ]); ?>
            <?php BackendSurfaceWidget::end(); ?>

            <?= CmsLogListWidget::widget([
                'query' => $model->getLogs()->comments(),
                'is_show_model' => false,
                'is_show_pin_controls' => false,
            ]); ?>
        <?php $commentsPjax::end(); ?>
    </main>
</div>
