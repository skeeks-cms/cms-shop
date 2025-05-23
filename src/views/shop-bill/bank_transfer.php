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
?>

<section class="sx-print-no-margin">
    <div class="container content">
        <div class="row">
            <div class="col-12 sx-container-wrapper">

                <p>Поставщик: <?= $model->receiverContractor->asShortText; ?><br/>
                    Юр. Адрес: <?= $model->receiverContractor->mailing_postcode; ?>, <?= $model->receiverContractor->address; ?>
                    <? if ($model->receiverContractor->ogrn) : ?>
                        <br />
                        <? if ($model->receiverContractor->contractor_type == \skeeks\cms\models\CmsContractor::TYPE_INDIVIDUAL) : ?>
                            ОГРНИП
                        <? elseif ($model->receiverContractor->contractor_type == \skeeks\cms\models\CmsContractor::TYPE_LEGAL) : ?>
                            ОГРН
                        <? endif; ?>
                        : <?= $model->receiverContractor->ogrn; ?>
                    <? endif; ?>
                </p>

                <p style="text-align: center; margin: 15px; 15px;"><b>Образец заполнения платежного поручения</b></p>

                <table border="2" class="bank-data-table" style="border-width: 2px;">
                    <tr>
                        <td rowspan="2" colspan="2" style="border-width: 2px;
    padding: 5px;
    vertical-align: top;">
                            <?= $model->receiverContractorBank->bank_name; ?><br/>
                            Банк получателя
                        </td>
                        <td>
                            БИК
                        </td>
                        <td rowspan="2">
                            <?= $model->receiverContractorBank->bic; ?><br/>
                            <?= $model->receiverContractorBank->correspondent_account; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Сч. №
                        </td>
                    </tr>


                    <tr>
                        <td>
                            ИНН <?= $model->receiverContractor->inn; ?>
                        </td>
                        <td>
                            КПП
                            <? if ($model->receiverContractor->kpp) : ?>
                                <?= $model->receiverContractor->kpp; ?>
                            <? endif; ?>
                        </td>
                        <td rowspan="3">
                            Сч. №
                        </td>
                        <td rowspan="3">
                            <?= $model->receiverContractorBank->checking_account; ?>
                        </td>
                    </tr>

                    <tr>
                        <td rowspan="2" colspan="2">
                            <?= $model->receiverContractor->asShortText; ?><br/>
                            Получатель
                        </td>
                    </tr>


                </table>

                <h1 style="text-align: center; margin-bottom: 15px; margin-top: 15px;">
                    <b>Счет №<?= $model->id; ?> от <?= \Yii::$app->formatter->asDate($model->created_at, "long"); ?></b>
                </h1>

                <p>Плательщик: <b><?= $model->senderContractor->asShortText; ?>, ИНН <?= $model->senderContractor->inn; ?></b></p>


                <table border="2" class="sx-positions">
                    <tr>
                        <th>№</th>
                        <th>Наименование товара</th>
                        <th>Кол-во</th>
                        <th>Ед.</th>
                        <th>Цена</th>
                        <th>Сумма</th>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td><?= (string)$model->description; ?></td>
                        <td>1</td>
                        <td>шт</td>
                        <td><?= (string)$model->money; ?></td>
                        <td><?= (string)$model->money; ?></td>
                    </tr>
                </table>

                <p style="text-align: right; font-weight: bold; margin-top: 10px;">
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
                    Всего наименований на сумму <?= (string)$model->money; ?>
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
                        <? if (!$noSignature && $model->receiverContractor->directorSignature) : ?>
                            <img src="<?= $model->receiverContractor->directorSignature->absoluteSrc; ?>" style="max-height: 60px; margin-bottom: -25px;"/>
                        <? else : ?>
                            <span style="width: 88px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        <? endif; ?>
                        <?= $model->receiverContractor->name; ?>
                    </div>
                    <? if ($model->receiverContractor->stamp && !$noSignature) : ?>
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

                    <div class="sx-controlls">
                        <button class="btn btn-primary btn-lg" onclick="window.print();" style="margin-right: 1rem;"><i class="fa fa-print"></i> Печать</button>

                        <a href="<?= \yii\helpers\Url::to(['pdf', 'code' => $model->code]) ?>" target="_blank" class="btn btn-primary btn-lg" style="margin-right: 1rem;">
                            <i class="fa fa-file"></i>
                            Скачать PDF
                        </a>

                        <?php if (\Yii::$app->user->can(\skeeks\cms\rbac\CmsManager::PERMISSION_ROLE_ADMIN_ACCESS)) : ?>
                            <a href="<?= \yii\helpers\Url::to(['pdf', 'code' => $model->code, 'noSignature' => '1']) ?>" target="_blank" class="btn btn-primary btn-lg">
                                <i class="fa fa-file"></i>
                                Скачать PDF (без подписей)
                            </a>
                        <?php endif; ?>
                        

                    </div>
                <? endif; ?>


            </div>
        </div>
    </div>
</section>