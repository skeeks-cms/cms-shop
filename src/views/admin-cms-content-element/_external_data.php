<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/* @var $this yii\web\View */
/* @var $data [] */
?>

<? foreach ($data as $key => $row) : ?>
    <? if ($row) : ?>
        <p><span><?= $key; ?>:</span>
        <? if (is_string($row)) : ?>
            <? if (filter_var($row, FILTER_VALIDATE_URL)) : ?>
                <b><a href="<?= $row; ?>" target="_blank"><?= $row; ?></a></b>
            <? else : ?>
                <b><?= $row; ?></b>
            <? endif; ?>

        <? else : ?>
            <pre><?= print_r($row, true); ?></pre>
        <? endif; ?>
        </p>
    <? endif; ?>

<? endforeach; ?>
