<?php

use skeeks\cms\backend\widgets\BackendSurfaceWidget;
use skeeks\cms\backend\widgets\BackendEntityLink;
use skeeks\cms\models\CmsLog;
use skeeks\cms\shop\models\ShopPartnerPayout;
use skeeks\cms\widgets\admin\CmsCommentWidget;
use skeeks\cms\widgets\admin\CmsLogListWidget;
use skeeks\cms\widgets\Pjax;
use yii\helpers\Html;

/** @var ShopPartnerPayout $model */

$this->title = 'Заявка на вывод №'.(int)$model->id;
$formatText = static function ($value): string {
    $value = trim((string)$value);
    return $value === '' ? '—' : nl2br(Html::encode($value));
};
?>

<div class="sx-detail-layout">
    <aside class="sx-detail-layout__aside sx-surface-stack">
        <?php BackendSurfaceWidget::begin(['title' => 'Заявка', 'titleTag' => 'h2', 'raised' => true, 'responsive' => true]); ?>
            <table class="sx-key-value-view">
                <tbody>
                    <tr>
                        <th>Сумма</th>
                        <td><strong><?= \Yii::$app->formatter->asDecimal((float)$model->value, 2); ?> бонусов</strong></td>
                    </tr>
                    <tr>
                        <th>Статус</th>
                        <td><span class="<?= ShopPartnerPayout::statusCssClass($model->status); ?>"><?= Html::encode($model->statusName); ?></span></td>
                    </tr>
                    <tr>
                        <th>Создана</th>
                        <td><?= $model->created_at ? \Yii::$app->formatter->asDatetime($model->created_at) : '—'; ?></td>
                    </tr>
                    <?php if ($model->paid_at) : ?>
                        <tr>
                            <th>Выплачена</th>
                            <td><?= \Yii::$app->formatter->asDatetime($model->paid_at); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if ($model->bonusTransaction) : ?>
                        <tr>
                            <th>Списание</th>
                            <td><?= Html::encode($model->bonusTransaction->asText()); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php BackendSurfaceWidget::end(); ?>

        <?php BackendSurfaceWidget::begin(['title' => 'Партнёр и реквизиты', 'titleTag' => 'h2', 'raised' => true, 'responsive' => true]); ?>
            <table class="sx-key-value-view">
                <tbody>
                    <tr>
                        <th>Партнёр</th>
                        <td>
                            <?= $model->cmsUser ? BackendEntityLink::widget([
                                'controllerId' => '/cms/admin-user',
                                'modelId' => (int)$model->cms_user_id,
                                'label' => $model->cmsUser->displayName,
                            ]) : '—'; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Реквизиты</th>
                        <td><?= $formatText($model->requisites); ?></td>
                    </tr>
                </tbody>
            </table>
        <?php BackendSurfaceWidget::end(); ?>

        <?php if ($model->status === ShopPartnerPayout::STATUS_PAID && $model->manager_comment) : ?>
            <?php BackendSurfaceWidget::begin(['title' => 'Сообщение партнёру', 'titleTag' => 'h2', 'raised' => true, 'responsive' => true]); ?>
                <?= $formatText($model->manager_comment); ?>
            <?php BackendSurfaceWidget::end(); ?>
        <?php elseif ($model->status === ShopPartnerPayout::STATUS_REJECTED && $model->reject_reason) : ?>
            <?php BackendSurfaceWidget::begin(['title' => 'Причина отмены', 'titleTag' => 'h2', 'raised' => true, 'responsive' => true]); ?>
                <?= $formatText($model->reject_reason); ?>
            <?php BackendSurfaceWidget::end(); ?>
        <?php endif; ?>
    </aside>

    <main class="sx-detail-layout__main">
        <?php $commentsPjax = Pjax::begin(['id' => 'partner-payout-comments', 'options' => ['class' => 'sx-surface-stack sx-activity-thread']]); ?>
            <?php BackendSurfaceWidget::begin([
                'title' => 'Написать партнёру',
                'hint' => 'Сообщение появится в карточке заявки и в уведомлениях партнёра.',
                'titleTag' => 'h2',
                'raised' => true,
                'responsive' => true,
            ]); ?>
                <?= CmsCommentWidget::widget([
                    'model' => $model,
                    'backend_url' => ['/shop/admin-partner-payout/add-comment', 'pk' => $model->id],
                    'isShowPin' => false,
                ]); ?>
            <?php BackendSurfaceWidget::end(); ?>

            <?= CmsLogListWidget::widget([
                'query' => $model->getLogs()->logType([CmsLog::LOG_TYPE_COMMENT]),
                'is_show_model' => false,
                'is_show_pin_controls' => false,
            ]); ?>
        <?php $commentsPjax::end(); ?>
    </main>
</div>
