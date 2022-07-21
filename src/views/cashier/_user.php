<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/**
 * @var $this yii\web\View
 * @var $model \skeeks\cms\models\CmsUser
 */
?>
<div class="item"
data-id="<?php echo $model->id; ?>"
>
    <!--<div class="sc-kafWEX iSpKLC">
        <i class="fa icon fa-star fa-fw"></i>
    </div>-->
    <div><span><?php echo $model->shortDisplayName; ?></span>
        <?php if($model->phone) : ?>
            <span class="phones"><?php echo $model->phone; ?></span>
        <?php endif; ?>
        <?php if($model->email) : ?>
            <span class="emails"><?php echo $model->email; ?></span>
        <?php endif; ?>
    </div>
</div>
