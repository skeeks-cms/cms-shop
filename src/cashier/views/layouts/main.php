<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */
\skeeks\cms\shop\cashier\assets\CashierAsset::register($this);
\skeeks\cms\widgets\user\UserOnlineTriggerWidget::widget();

/**
 * @var $theme \skeeks\cms\themes\unify\admin\UnifyThemeAdmin;
 */
$theme = $this->theme;
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>" prefix="og: http://ogp.me/ns#">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <!--<link rel="icon" href="<? /*= $theme->favicon */ ?>" type="image/x-icon"/>-->
        <?php $this->head() ?>
    </head>
    <body class="theme-light">
        <?php $this->beginBody() ?>
            <?= $content; ?>
        <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>