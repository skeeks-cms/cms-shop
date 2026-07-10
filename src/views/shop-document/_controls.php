<?php
/* @var $model \skeeks\cms\shop\models\ShopDocument */

use skeeks\cms\rbac\CmsManager;
use yii\helpers\Url;
?>

<div class="sx-controlls">
    <a href="<?= Url::to(['pdf', 'code' => $model->code]); ?>" target="_blank" class="btn btn-primary btn-lg">
        <i class="fa fa-file"></i> Скачать PDF
    </a>
    <?php if (\Yii::$app->user->can(CmsManager::PERMISSION_ROLE_ADMIN_ACCESS)) : ?>
        <a href="<?= Url::to(['pdf', 'code' => $model->code, 'noSignature' => '1']); ?>" target="_blank" class="btn btn-primary btn-lg">
            <i class="fa fa-file"></i> Скачать PDF без подписей
        </a>
    <?php endif; ?>
</div>
