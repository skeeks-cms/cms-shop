<?php
/**
 * @var $this yii\web\View
 * @var $model \skeeks\cms\shop\models\ShopBrand
 */
$controller = $this->context;
$image = $model->logo;
?>
<div class="row sx-model-header">
    <?php if ($image && $image->src) : ?>
        <div class="col my-auto sx-model-header__media">
            <img class="sx-model-header__image" src="<?php echo \yii\helpers\Html::encode($image->src); ?>"/>
        </div>
    <?php endif; ?>
    <div class="col my-auto">
        <div class="d-flex">
            <div>
                <h1 class="sx-model-header__title">
                    <?php echo $controller->modelShowName; ?>
                    <?php if ($model->sx_id) : ?>
                        <?php
                        $sxInfoUpdateClass = $model->is_sx_info_update
                            ? "sx-text--success"
                            : "sx-text--danger";
                        $sxInfoUpdateTitle = $model->is_sx_info_update
                            ? "SkeekS ID: {$model->sx_id}. Информация обновляется из сервиса SkeekS Товары"
                            : "SkeekS ID: {$model->sx_id}. Обновление информации из сервиса SkeekS Товары запрещено";
                        $sxMarketUrl = isset(\Yii::$app->skeeksSuppliersApi) ? \Yii::$app->skeeksSuppliersApi->getBrandUrl($model->sx_id) : "#";
                        $sxIcon = "<i class='fas fa-link {$sxInfoUpdateClass}'></i>";
                        ?>
                        <span class="sx-model-header__external-id">
                            <?php echo \yii\helpers\Html::a($sxIcon, $sxMarketUrl, [
                                'target' => '_blank',
                                'data-pjax' => '0',
                                'data-toggle' => 'tooltip',
                                'title' => $sxInfoUpdateTitle,
                                'class' => $sxInfoUpdateClass,
                            ]); ?>
                        </span>
                    <?php endif; ?>
                </h1>
                <div class="sx-small-info sx-model-header__meta">
                    <span title="ID записи - уникальный код записи в базе данных." data-toggle="tooltip"><i class="fas fa-key"></i> <?php echo $model->id; ?></span>
                    <?php if ($model->created_at) : ?>
                        <span data-toggle="tooltip" title="Запись создана в базе: <?php echo \Yii::$app->formatter->asDatetime($model->created_at); ?>"><i class="far fa-clock"></i> <?php echo \Yii::$app->formatter->asDate($model->created_at); ?></span>
                    <?php endif; ?>
                    <?php if ($model->created_by) : ?>
                        <span data-toggle="tooltip" title="Запись создана пользователем с ID: <?php echo $model->createdBy->id; ?>"><i class="far fa-user"></i> <?php echo $model->createdBy->shortDisplayName; ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($model->absoluteUrl) : ?>
                <div class="col my-auto sx-model-header__actions">
                    <a href="<?php echo $model->absoluteUrl; ?>" data-toggle="tooltip" class="btn btn-default" target="_blank" data-pjax="0" title="<?php echo \Yii::t('skeeks/cms', 'Watch to site (opens new window)'); ?>">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php
    $modelActions = $controller->modelActions;
    $deleteAction = \yii\helpers\ArrayHelper::getValue($modelActions, "delete");

    if ($deleteAction) : ?>
        <?php
        $actionData = [
            "url"             => $deleteAction->url,
            "isOpenNewWindow" => true,
            "confirm"         => isset($deleteAction->confirm) ? $deleteAction->confirm : "",
            "method"          => isset($deleteAction->method) ? $deleteAction->method : "",
            "request"         => isset($deleteAction->request) ? $deleteAction->request : "",
            "size"            => isset($deleteAction->size) ? $deleteAction->size : "",
        ];
        $actionData = \yii\helpers\Json::encode($actionData);

        $href = \yii\helpers\Html::a('<i class="fa fa-trash sx-action-icon"></i>', "#", [
            'onclick'     => "new sx.classes.backend.widgets.Action({$actionData}).go(); return false;",
            'class'       => "btn btn-default",
            'data-toggle' => "tooltip",
            'title'       => \Yii::t('skeeks/cms', 'Delete'),
        ]);
        ?>
        <div class="col my-auto sx-model-header__actions">
            <?php echo $href; ?>
        </div>
    <?php endif; ?>
</div>
