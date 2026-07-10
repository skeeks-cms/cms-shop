<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopBill */

if (!@$isPdf) {
    $css = file_get_contents(\Yii::getAlias('@skeeks/cms/shop/views/shop-bill/bank_transfer.css'));
    $this->registerCss($css);
}

$noSignature = @$noSignature;
$billItems = $model->printableBillItems;
$hasItemDiscounts = false;
foreach ($billItems as $billItem) {
    if ((float)$billItem->discount_amount > 0) {
        $hasItemDiscounts = true;
        break;
    }
}
$hasBillDiscount = (float)$model->discount_amount > 0;
$hasDiscounts = $hasItemDiscounts || $hasBillDiscount;
$hasDescription = trim((string)$model->description) !== '';
?>

<section class="sx-print-no-margin">
    <div class="container content">
        <div class="row">
            <div class="col-12 sx-container-wrapper">

                <?php if ($model->due_at) : ?>
                    <p style="text-align: center; font-weight: bold; margin-bottom: 20px;">
                        Оплату необходимо произвести до <?= \Yii::$app->formatter->asDate($model->due_at, "long"); ?>
                    </p>
                <?php endif; ?>

                <p>Поставщик: <?= \yii\helpers\Html::encode($model->billReceiverName); ?><br/>
                    Юр. Адрес: <?= \yii\helpers\Html::encode($model->billReceiverPostcode); ?>, <?= \yii\helpers\Html::encode($model->billReceiverAddress); ?>
                    <? if ($model->billReceiverOgrn) : ?>
                        <br />
                        <?= \yii\helpers\Html::encode($model->billReceiverOgrnLabel); ?>: <?= \yii\helpers\Html::encode($model->billReceiverOgrn); ?>
                    <? endif; ?>
                </p>

                <table border="2" class="bank-data-table" style="border-width: 2px;">
                    <tr>
                        <td rowspan="2" colspan="2" style="border-width: 2px;
    padding: 5px;
    vertical-align: top;">
                            <?= \yii\helpers\Html::encode($model->billReceiverBankName); ?><br/>
                            Банк получателя
                        </td>
                        <td>
                            БИК
                        </td>
                        <td rowspan="2">
                            <?= \yii\helpers\Html::encode($model->billReceiverBankBic); ?><br/>
                            <?= \yii\helpers\Html::encode($model->billReceiverBankCorrespondentAccount); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Сч. №
                        </td>
                    </tr>


                    <tr>
                        <td>
                            ИНН <?= \yii\helpers\Html::encode($model->billReceiverInn); ?>
                        </td>
                        <td>
                            КПП
                            <? if ($model->billReceiverKpp) : ?>
                                <?= \yii\helpers\Html::encode($model->billReceiverKpp); ?>
                            <? endif; ?>
                        </td>
                        <td rowspan="3">
                            Сч. №
                        </td>
                        <td rowspan="3">
                            <?= \yii\helpers\Html::encode($model->billReceiverBankCheckingAccount); ?>
                        </td>
                    </tr>

                    <tr>
                        <td rowspan="2" colspan="2">
                            <?= \yii\helpers\Html::encode($model->billReceiverName); ?><br/>
                            Получатель
                        </td>
                    </tr>


                </table>

                <h1 style="text-align: center; margin-bottom: 15px; margin-top: 15px;">
                    <b>Счет №<?= $model->id; ?> от <?= \Yii::$app->formatter->asDate($model->created_at, "long"); ?></b>
                </h1>

                <?php if($model->billSenderName) : ?>
                    <p>Плательщик: <b><?= \yii\helpers\Html::encode($model->billSenderName); ?>, ИНН <?= \yii\helpers\Html::encode($model->billSenderInn); ?></b></p>
                <?php endif; ?>

                <?php if ($hasDescription) : ?>
                    <p style="margin: 8px 0 12px 0;"><?= nl2br(\yii\helpers\Html::encode($model->description)); ?></p>
                <?php endif; ?>


                <table border="2" class="sx-positions">
                    <tr>
                        <th>№</th>
                        <th>Наименование товара</th>
                        <th>Кол-во</th>
                        <th>Ед.</th>
                        <th>Цена</th>
                        <?php if ($hasDiscounts) : ?>
                            <th>Скидка</th>
                        <?php endif; ?>
                        <th>Сумма</th>
                    </tr>
                    <?php foreach ($billItems as $index => $item) : ?>
                        <tr>
                            <td><?= $index + 1; ?></td>
                            <td><?= \yii\helpers\Html::encode($item->name); ?></td>
                            <td><?= (float)$item->quantity; ?></td>
                            <td><?= \yii\helpers\Html::encode($item->measure_name); ?></td>
                            <td><?= (string)$item->priceMoney; ?></td>
                            <?php if ($hasDiscounts) : ?>
                                <td><?= (float)$item->discount_amount > 0 ? (string)$item->discountMoney : ''; ?></td>
                            <?php endif; ?>
                            <td><?= (string)$item->money; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>

                <p style="text-align: right; font-weight: bold; margin-top: 10px;">
                    <?php if ($hasBillDiscount) : ?>
                        <span style="display: block; font-weight: normal;">
                            Скидка по счету: -<?= (string)(new \skeeks\cms\money\Money($model->discount_amount, (string)$model->currency_code)); ?>
                        </span>
                    <?php endif; ?>
                    Итого: <?= (string)$model->money; ?>
                </p>
                <?/* if ($model->crmVat) : */?><!--
                    <p style="text-align: right;">
                        <?/*
                        $moneyNDS = clone $model->money;
                        $moneyNoVat = clone $model->money;
                        $moneyNDS->mul($model->crmVat->rate / (100 + $model->crmVat->rate) );
                        $moneyNoVat->sub($moneyNDS);
                        */?>
                        Итого без налогов: <?/*= $moneyNoVat; */?>
                    </p>
                    <p style="text-align: right;">
                        <?/*= $model->crmVat->name; */?>: <?/*= $moneyNDS; */?>
                    </p>

                --><?/* else : */?>
                    <p style="text-align: right;">
                        Без НДС: 0
                    </p>
                <?/* endif; */?>

                <p style="">
                    Всего наименований: <?= count($billItems); ?>, на сумму <?= (string)$model->money; ?>
                </p>
                <p style="font-weight: bold;">
                    <?
                    $f = new NumberFormatter("ru", NumberFormatter::SPELLOUT);
                    echo \skeeks\cms\helpers\StringHelper::ucfirst($f->format($model->money->amount)." ");
                    /*echo Yii::t("app",
                        "{n, plural, =0{рублей} =1{рубль} one{рубль} few{рубля} many{рублей1} other{рубля2}}",
                        ['n' => (int) $model->money->amount]);*/
                    ?>
                    руб.
                </p>

                <hr style="margin: 20px 0; border-top: 2px solid #333;"/>

                <div style="font-weight: bold;" class="row">
                    <div class="col-md-12" style="line-height: 60px;">
                        Выписал
                        <? if (!$noSignature && $model->receiverContractor && $model->receiverContractor->directorSignature) : ?>
                            <img src="<?= $model->receiverContractor->directorSignature->absoluteSrc; ?>" style="max-height: 60px; margin-bottom: -25px;"/>
                        <? else : ?>
                            <span style="width: 88px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        <? endif; ?>
                        <?= \yii\helpers\Html::encode($model->billReceiverName); ?>
                    </div>
                    <? if ($model->receiverContractor && $model->receiverContractor->stamp && !$noSignature) : ?>
                        <div class="col-md-12" style="line-height: 60px;">
                            <img src="<?= $model->receiverContractor->stamp->absoluteSrc; ?>" style="max-height: 140px; margin-top: -100px; margin-right: 50px; float: right;"/>
                        </div>
                    <? endif; ?>


                </div>


                <? if (!$isPdf) : ?>
                    <? if ($model->closed_at) : ?>
                        <div class="" style="color: red; font-weight: bold;">Счет не оплачен и отменен <?= \Yii::$app->formatter->asDatetime($model->closed_at); ?></div>
                    <? elseif ($model->paid_at) : ?>
                        <div class="" style="color: green; font-weight: bold;">Счет оплачен <?= \Yii::$app->formatter->asDate($model->paid_at); ?></div>
                    <? else: ?>
                        
                    <? endif; ?>

                <? endif; ?>


            </div>
        </div>
    </div>
</section>

<?php if (!$isPdf) : ?>
    <div class="sx-controlls">
        <a href="<?= \yii\helpers\Url::to(['pdf', 'code' => $model->code]) ?>" target="_blank" class="btn btn-primary btn-lg">
            <i class="fa fa-file"></i> Скачать PDF
        </a>
        <?php if (\Yii::$app->user->can(\skeeks\cms\rbac\CmsManager::PERMISSION_ROLE_ADMIN_ACCESS)) : ?>
            <a href="<?= \yii\helpers\Url::to(['pdf', 'code' => $model->code, 'noSignature' => '1']) ?>" target="_blank" class="btn btn-primary btn-lg">
                <i class="fa fa-file"></i> Скачать PDF без подписей
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>
