<?php
/**
 * Карточка заявки партнёрской программы.
 *
 * @var $this yii\web\View
 * @var $model \skeeks\cms\models\CmsLead
 */

use skeeks\cms\models\CmsLead;
use skeeks\cms\shop\models\ShopPartnerLead;
use skeeks\cms\backend\widgets\BackendSurfaceWidget;
use skeeks\cms\widgets\admin\CmsCommentWidget;
use skeeks\cms\widgets\admin\CmsLeadStatusWidget;
use skeeks\cms\widgets\admin\CmsLogListWidget;
use skeeks\cms\widgets\Pjax;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$isActionWindow = (bool)ArrayHelper::getValue((array)\Yii::$app->request->get('_sxb'), 'el');
$statusDescription = (string)ArrayHelper::getValue(
    CmsLead::statusDescriptions(),
    $model->status,
    ''
);

$this->title = 'Заявка №'.(int)$model->id;

$this->registerCss(<<<CSS
body.sx-empty .sx-content-wrapper {
    padding-top: 16px;
}
.sx-partner-lead-header {
    align-items: flex-start;
    display: flex;
    gap: 1rem;
    justify-content: space-between;
    margin-bottom: 1rem;
}
.sx-partner-lead-header__title {
    margin: 0 0 0.5rem;
}
.sx-partner-lead-note {
    margin-top: 1rem;
}
.sx-partner-lead-description {
    white-space: pre-line;
}
CSS
);
?>

<div class="sx-partner-lead-header">
    <div>
        <h1 class="sx-partner-lead-header__title"><?= Html::encode($model->name); ?></h1>
        <?= CmsLeadStatusWidget::widget(['lead' => $model]); ?>
        <?php if ($statusDescription) : ?>
            <p class="sx-surface__hint" style="margin-top: 0.5rem;"><?= Html::encode($statusDescription); ?></p>
        <?php endif; ?>
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

<div class="sx-surface sx-surface--padded">
    <table class="sx-key-value-view">
        <tbody>
            <tr>
                <th>Номер заявки</th>
                <td><?= (int)$model->id; ?></td>
            </tr>
            <tr>
                <th>Добавлена</th>
                <td><?= $model->created_at ? \Yii::$app->formatter->asDatetime($model->created_at) : '—'; ?></td>
            </tr>
            <tr>
                <th>Телефоны</th>
                <td>
                    <?php if ($model->phones) : ?>
                        <?php foreach ($model->phones as $phone) : ?>
                            <div>
                                <?= Html::a(
                                    Html::encode($phone->value),
                                    'tel:'.preg_replace('/[^\d+]/', '', $phone->value),
                                    ['data-pjax' => '0']
                                ); ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        —
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Email</th>
                <td>
                    <?php if ($model->emails) : ?>
                        <?php foreach ($model->emails as $email) : ?>
                            <div>
                                <?= Html::a(
                                    Html::encode($email->value),
                                    'mailto:'.$email->value,
                                    ['data-pjax' => '0']
                                ); ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        —
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Что может быть полезно клиенту</th>
                <td>
                    <div class="sx-partner-lead-description">
                        <?= $model->description ? Html::encode($model->description) : '—'; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>Вознаграждение</th>
                <td>
                    <?php $partnerReward = ShopPartnerLead::find()->andWhere(['cms_lead_id' => $model->id])->one(); ?>
                    <?php if ($partnerReward) : ?>
                        <strong class="sx-text--success">
                            +<?= \Yii::$app->formatter->asDecimal((float)$partnerReward->reward_value, 2); ?> бонусов
                        </strong>
                    <?php elseif (in_array($model->status, [CmsLead::STATUS_NEW, CmsLead::STATUS_IN_WORK], true)) : ?>
                        Будет начислено после продажи услуги
                    <?php else : ?>
                        —
                    <?php endif; ?>
                </td>
            </tr>
            <?php if ($model->status === CmsLead::STATUS_REJECTED && $model->reject_reason) : ?>
                <tr>
                    <th>Причина отклонения</th>
                    <td><?= Html::encode($model->reject_reason); ?></td>
                </tr>
            <?php endif; ?>
            <?php if ($model->result_comment) : ?>
                <tr>
                    <th>Комментарий менеджера</th>
                    <td><?= Html::encode($model->result_comment); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($model->status === CmsLead::STATUS_SUCCESS) : ?>
    <div class="alert alert-success sx-partner-lead-note">
        Бонусы уже на вашем балансе. Их можно направить на оплату наших услуг
        или запросить вывод деньгами: 1 бонус = 1 рубль.
        <?= Html::a('Мои бонусы', ['/shop/upa-partner-bonus'], ['data-pjax' => '0']); ?>
    </div>
<?php elseif ($model->status === CmsLead::STATUS_NEW) : ?>
    <div class="alert alert-info sx-partner-lead-note">
        Заявка получена и ожидает ответственного менеджера. Когда менеджер возьмёт её в работу,
        статус изменится, а дальнейшие сообщения появятся в активности заявки.
    </div>
<?php elseif ($model->status === CmsLead::STATUS_IN_WORK) : ?>
    <div class="alert alert-info sx-partner-lead-note">
        Заявка в работе: мы связываемся с потенциальным клиентом, обсуждаем задачи и условия.
        Как только услуга будет продана, вознаграждение автоматически появится на бонусном балансе.
    </div>
<?php endif; ?>

<?php $commentsPjax = Pjax::begin([
    'id' => 'sx-partner-lead-comments',
    'options' => ['class' => 'sx-surface-stack sx-activity-thread sx-partner-lead-note'],
]); ?>
    <?php BackendSurfaceWidget::begin([
        'title' => 'Написать менеджеру',
        'hint' => 'Задайте вопрос или уточните информацию по этой заявке.',
        'titleTag' => 'h2',
        'raised' => true,
        'responsive' => true,
    ]); ?>
        <?= CmsCommentWidget::widget([
            'model' => $model,
            'backend_url' => ['/shop/upa-partner-lead/add-comment', 'pk' => $model->id],
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
