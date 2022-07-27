<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

//$class = $this->theme->themeAssetClass;
//$class::register($this);
/* @var $this \yii\web\View */
/* @var $content string */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ценник 58x40 мм</title>
    <meta charset="UTF-8">
    <?php echo $this->render("_style", [
        'isPrintSpec' => $isPrintSpec,
    ]); ?>
</head>
<body>
<?php echo $this->render("_settings", [
    'isPrintSpec' => $isPrintSpec,
]); ?>
<?= $content; ?>
<?php echo $this->render("_scripts"); ?>
</body>
</html>
