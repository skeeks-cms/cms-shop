<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/**
 * @var $model \skeeks\cms\shop\models\ShopOrder
 */
?>

<div class="item col-12 sx-item-wrapper">
    <a href="<?php echo \yii\helpers\Url::to(['view', 'pk' => $model->id]); ?>" class="sx-item">
        <div class="sx-text-wrapper">
            <div class="row">
                <div class="col-12 col-sm-8 my-auto">

                    <div class="sx-title h5" style="margin-bottom: 0;">
                        Заказ №<?php echo $model->id; ?> на сумму <?php echo $model->money; ?>
                    </div>
                    <div class="sx-description">
                        <small><?php echo \Yii::$app->formatter->asDatetime($model->created_at); ?></small>
                    </div>
                </div>
                <div class="col-12 col-sm-4 my-auto">
                    <div class="float-right">
                        <div class="btn sx-status" style="background: <?php echo $model->shopOrderStatus->bg_color; ?>; color: <?php echo $model->shopOrderStatus->color; ?>;">
                            <?php echo $model->shopOrderStatus->name; ?>
                        </div>
                        <div class="sx-status-info">
                            
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </a>
</div>