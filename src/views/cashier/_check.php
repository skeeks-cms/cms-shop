<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/**
 * @var $this yii\web\View
 * @var $model \skeeks\cms\shop\models\ShopCheck
 */
?>

<div class="sx-view-check">


    <div class="sx-row">
        <div class="sx-first-column">Кассовый чек / <?php echo $model->docTypeAsText; ?></div>
        <div><?php echo \Yii::$app->formatter->asDatetime($model->created_at, "php:d.m.Y h:i"); ?></div>
    </div>

    <?php if ($model->seller_name || $model->kkm_payments_address || $model->seller_address) : ?>
    <div class="sx-check-head-data">
        <?php endif; ?>

        <?php if ($model->seller_name) : ?>
            <div class="sx-seller_name text-center">
                <?php echo $model->seller_name; ?>
            </div>
        <?php endif; ?>
        <?php if ($model->kkm_payments_address) : ?>
            <div class="sx-kkm_payments_address text-center">
                <?php echo $model->kkm_payments_address; ?>
            </div>
        <?php endif; ?>
        <?php if ($model->seller_address) : ?>
            <div class="sx_seller_address text-center">
                <?php echo $model->seller_address; ?>
            </div>
        <?php endif; ?>


        <?php if ($model->seller_name || $model->kkm_payments_address || $model->seller_address) : ?>
    </div>
<?php endif; ?>

    <?php if ($model->seller_inn) : ?>
            <div class="sx-row">
                <div class="sx-first-column">ИНН</div>
                <div><?php echo $model->seller_inn; ?></div>
            </div>
        <?php endif; ?>


    <?php if ($model->inventPositions) : ?>
        <?php $positions = $model->inventPositions; ?>
        <table class="sx-postions-table">
            <tr>
                <th>№</th>
                <th>Наименование</th>
                <th>Сумма</th>
            </tr>
            <tbody>
            <?php
            $counter = 0;
            foreach ($positions as $one) : ?>
                <? $counter++; ?>
                <?
                $itemPrice = (float) \yii\helpers\ArrayHelper::getValue($one, "price") - (float) \yii\helpers\ArrayHelper::getValue($one, "discSum");
                $sum = $itemPrice * \yii\helpers\ArrayHelper::getValue($one, "quantity");
                $sum = round($sum, 2);
                ?>
                <tr>
                    <td><?php echo $counter; ?></td>
                    <td>
                        <div><?php echo \yii\helpers\ArrayHelper::getValue($one, "name"); ?></div>
                        <div><?php echo \skeeks\cms\shop\models\ShopCheck::getPaymentObjectAsText(\yii\helpers\ArrayHelper::getValue($one, "paymentObject")); ?> /
                            <?php echo \skeeks\cms\shop\models\ShopCheck::getPaymentMethodAsText(\yii\helpers\ArrayHelper::getValue($one, "paymentMethod")); ?></div>
                    </td>
                    <td><?php echo $itemPrice; ?> x <?php echo \yii\helpers\ArrayHelper::getValue($one, "quantity"); ?> = <?php echo $sum; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="sx-check-results">

        <div class="sx-row">
            <div class="sx-first-column">Итого</div>
            <div><?php echo $model->amount; ?></div>
        </div>
        <div class="sx-row">
            <div class="sx-first-column">Сумма без НДС</div>
            <div><?php echo $model->amount; ?></div>
        </div>

        <?php if ($model->moneyPositions) : ?>
            <?php foreach ($model->moneyPositions as $moneyData) : ?>
                <div class="sx-row">
                    <div class="sx-first-column">
                        <?php echo \skeeks\cms\shop\models\ShopCheck::getPaymentTypeAsText(\yii\helpers\ArrayHelper::getValue($moneyData, "paymentType")); ?>
                    </div>
                    <div><?php echo \yii\helpers\ArrayHelper::getValue($moneyData, "sum"); ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($model->cashier_name) : ?>
        <div class="sx-row">
            <div class="sx-first-column">
                <?php echo $model->cashier_position; ?>
            </div>
            <div><?php echo $model->cashier_name; ?></div>
        </div>
    <?php endif; ?>
    <?php if ($model->fiscal_shift_number) : ?>
        <div class="sx-row">
            <div class="sx-first-column">
                Номер смены
            </div>
            <div><?php echo $model->fiscal_shift_number; ?></div>
        </div>
    <?php endif; ?>
    <?php if ($model->fiscal_fn_doc_number) : ?>
        <div class="sx-row">
            <div class="sx-first-column">
                Номер ФД
            </div>
            <div><?php echo $model->fiscal_fn_doc_number; ?></div>
        </div>
    <?php endif; ?>
    <?php if ($model->fiscal_fn_doc_mark) : ?>
        <div class="sx-row">
            <div class="sx-first-column">
                ФПД:
            </div>
            <div><?php echo $model->fiscal_fn_doc_mark; ?></div>
        </div>
    <?php endif; ?>
    <?php if ($model->fiscal_check_number) : ?>
        <div class="sx-row">
            <div class="sx-first-column">
                Номер ФД в смене
            </div>
            <div><?php echo $model->fiscal_check_number; ?></div>
        </div>
    <?php endif; ?>


    <?php if ($model->fiscal_ecr_registration_umber) : ?>
        <div class="sx-row">
            <div class="sx-first-column">
                Рег. номер ККТ в ФНС
            </div>
            <div><?php echo $model->fiscal_ecr_registration_umber; ?></div>
        </div>
    <?php endif; ?>
    <?php if ($model->fiscal_kkt_number) : ?>
        <div class="sx-row">
            <div class="sx-first-column">
                Сер. номер ККТ
            </div>
            <div><?php echo $model->fiscal_kkt_number; ?></div>
        </div>
    <?php endif; ?>
    <?php if ($model->fiscal_fn_number) : ?>
        <div class="sx-row">
            <div class="sx-first-column">
                Сер. номер ФН
            </div>
            <div><?php echo $model->fiscal_fn_number; ?></div>
        </div>
    <?php endif; ?>

    <?php if ($model->email) : ?>
        <div class="sx-row">
            <div class="sx-first-column">
                Эл. адрес покупателя:
            </div>
            <div><?php echo $model->email; ?></div>
        </div>
    <?php endif; ?>



    <?php if ($model->qr) : ?>
        <?php $qrCodeBase64 = (new \chillerlan\QRCode\QRCode())->render($model->qr); ?>
        <div class="text-center sx-qr-wrapper">
            <img src="<?php echo $qrCodeBase64; ?>" class="sx-qrcode">
        </div>
    <?php endif; ?>


</div>