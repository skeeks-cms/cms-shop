<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/**
 * @var $this yii\web\View
 * @var $controller \skeeks\cms\shop\controllers\CashierController
 */

$controller = $this->context;

$jsData = \yii\helpers\Json::encode([
    'backend_products'    => \yii\helpers\Url::to(['products']),
    'backend_close_shift' => \yii\helpers\Url::to(['close-shift']),
    'backend-find-users'  => \yii\helpers\Url::to(['users']),

    'backend-add-product'       => \yii\helpers\Url::to(['add-product']),
    'backend-add-product-barcode'       => \yii\helpers\Url::to(['add-product-barcode']),
    'backend-remove-order-item' => \yii\helpers\Url::to(['remove-order-item']),
    'backend-clear-order-items' => \yii\helpers\Url::to(['clear-order-items']),
    'backend-update-order-item' => \yii\helpers\Url::to(['update-order-item']),
    'backend-update-order-user' => \yii\helpers\Url::to(['update-order-user']),
    'backend-update-order-data' => \yii\helpers\Url::to(['update-order-data']),
    'backend-order-create'      => \yii\helpers\Url::to(['order-create']),

    'backend-check-status'      => \yii\helpers\Url::to(['check-status']),
    'backend-get-order-item-edit'      => \yii\helpers\Url::to(['get-order-item-edit']),

    'order' => $controller->order->jsonSerialize(),
]);

$this->registerJs(<<<JS
sx.CashierApp = new sx.classes.CashierApp({$jsData});
JS
);
?>
<div class="sx-root">
    <div class="loadingBar waiting">
        <div class="message"><i class="fa icon fa-spinner fa-fw fa-spin"></i><span></span></div>
    </div>
    <div class="sx-content-wrapper">
        <div class="col-products">
            <div class="sx-block-search">
                <div class="action styl-material">
                    <div class="main-tabs">
                        <div name="catalog" icon="cubes" class="main-tabs-item"><i class="fa icon fa-cubes fa-fw"></i></div>
                        <div name="categories" icon="tags" class="main-tabs-item"><i class="fa icon fa-tags fa-fw"></i></div>
                        <div name="groups" icon="folder" class="main-tabs-item main-tabs-item--active"><i class="fa icon fa-folder fa-fw"></i></div>
                    </div>
                </div>
                <input type="text" autocomplete="off" name="search" placeholder="Поиск по наименованию, артикулу, штрихкоду, коду и описанию"/>
            </div>
            <div class="sx-block-products">
                <div class="catalogList">Список товаров</div>
            </div>
            <div class="sx-block-status-bar">
                <div class="d-flex sx-status-bar">
                    <div class="sx-menu">
                        <div class="sx-menu-btn">
                            <i class="fa icon fa-bars fa-fw"></i> Меню
                        </div>
                        <div class="sx-menu-content sx-closed">
                            <div class="sx-menu-user-block">
                                <div class="image-wrapper" style="height: 44px; width: 44px; border-radius: 22px;">
                                    <?php if (\Yii::$app->user->identity->image) : ?>
                                        <img class="image" src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=="
                                             style="background: url('<?php echo \Yii::$app->user->identity->avatarSrc; ?>'); background-size: cover;
                                                     background-repeat: no-repeat;
                                                     background-position: 50% 50%;">
                                    <?php else : ?>
                                        <img class="image" src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==">
                                    <?php endif; ?>

                                    <i
                                            class="animate fa icon fa-image fa-fw" style="font-size: 26.4px;"></i></div>
                                <div><span><?php echo \Yii::$app->user->identity->shortDisplayName; ?></span><br/>
                                    <a href="<?= \yii\helpers\Url::to(['/cms/auth/logout']) ?>" data-method="post">Выйти</a>
                                </div>
                            </div>
                            <div class="sc-ckVGcZ doNOSw"></div>


                            <?php if ($controller->shift) : ?>
                                <div class="sx-menu-item sx-close-shift-btn sx-red"><i class="fa icon fa-times fa-fw"></i>Закрыть смену</div>
                                <div class="sx-menu-item"
                                     id="sx-repeat-btn"
                                     data-sale="Создать возврат" data-return="Вернуться к продаже"
                                     data-return-val="<?php echo \skeeks\cms\shop\models\ShopOrder::TYPE_RETURN; ?>"
                                     data-sale-val="<?php echo \skeeks\cms\shop\models\ShopOrder::TYPE_SALE; ?>"
                                >
                                    <i class="fas icon fa-redo fa-fw"></i><span>Создать возврат</span>
                                </div>
                            <?php endif; ?>


                            <!--<div class="sc-dxgOiQ hEwctr"><i class="fa icon fa-repeat fa-fw"></i>Создать возврат</div>
                            <div class="sc-ckVGcZ doNOSw"></div>
                            <a class="sc-dxgOiQ hEwctr" href="/pos/home/settings/base"><i class="fa icon fa-cogs fa-fw"></i>Настройки</a><a class="sc-dxgOiQ hEwctr" href="/pos/home/shift"><i
                                        class="fa icon fa-clone fa-fw"></i>Смены</a></div>-->
                        </div>
                    </div>
                    <div class="sx-status">

                        <div class="sx-status-text">
                                <span class="">
                                    <span><?php echo \Yii::$app->shop->backendShopStore->name; ?></span>
                                </span>
                            <? if ($controller->shift) : ?>
                                <span style="margin-left: 5px;">/ <?php echo $controller->shift->shopCashebox->name ?></span>
                                <span style="margin-left: 5px;">/ #<?php echo $controller->shift->shift_number ?></span>
                            <? endif; ?>

                        </div>
                    </div>
                    <div class="sx-date">
                        <?php echo \Yii::$app->formatter->asDatetime(time()); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="checkout">
            <div class="sx-checkout-wrapper">

                <div class="sx-block-client clients">
                    <div class="dropdown">

                        <input type="text" placeholder="Поиск покупателя по имени, телефону, email и дисконтной карте" class="sx-user-search" value="">

                        <div class="sx-user-selected sx-user-selected-element">
                            <i class="fa icon fa-search fa-fw"></i>
                            <?php if ($controller->order->cmsUser) : ?>
                                <div class="sx-user"><?php echo $controller->order->cmsUser->shortDisplayName; ?></div>
                            <?php else : ?>
                                <div class="sx-user sx-user-not-selected">Выбрать покупателя</div>
                            <?php endif; ?>


                        </div>
                        <i class="fa icon fa-user-plus fa-fw sx-btn-hover sx-user-selected-element"></i></div>

                    <a href="" class="sx-user-find-clear sx-user-find-element">
                        <i class="fa icon fa-times fa-fw"></i>
                    </a>
                    <div class="sx-user-find-menu sx-user-find-element" style="position: relative; height: 100%;">
                    </div>

                </div>


                <div class="sx-block-cart sx-order-items-wrapper">

                </div>


                <div class="sx-block-checkout Checkout">
                    <div class="calculation">
                        <div class="row no-gutters">
                            <div class="col-12 sx-order-result-block <?php echo $controller->order->moneyItems->amount > 0 ? "" : "sx-hidden"; ?>">
                                <div class="float-right sx-money-items" data-value="<?= (float)$controller->order->moneyItems->amount; ?>">
                                    <?= $controller->order->moneyItems; ?>
                                </div>
                                <div class="pull-left">Предитог</div>
                            </div>
                            <div class="col-12 sx-order-result-block <?php echo \Yii::$app->shop->shopUser->moneyDelivery->amount > 0 ? "" : "sx-hidden"; ?>">
                                <div class="float-right sx-money-delivery" data-value="<?= (float)$controller->order->moneyDelivery->amount; ?>">
                                    <?= $controller->order->moneyDelivery; ?>
                                </div>
                                <div class="pull-left">Доставка</div>
                            </div>
                            <div class="col-12 sx-order-result-block <?php echo \Yii::$app->shop->shopUser->moneyVat->amount > 0 ? "" : "sx-hidden"; ?>">
                                <div class="float-right sx-money-vat" data-value="<?= (float)$controller->order->moneyVat->amount; ?>">
                                    <?= $controller->order->moneyVat; ?>
                                </div>
                                <div class="pull-left">Налог</div>
                            </div>
                            <div class="col-12 sx-order-result-block-visible">
                                <div class="float-right sx-order-result-total-percent">
                                    (<span class="sx-money-discount-percent" data-value="<?= (float)$controller->order->discount_percent_round; ?>"><?= (float)$controller->order->discount_percent_round; ?></span>%)
                                    <span class="sx-money-discount" data-value="<?= (float)$controller->order->moneyDiscount->amount; ?>"><?= $controller->order->moneyDiscount; ?></span>
                                </div>
                                <div class="pull-left">Скидка</div>
                            </div>
                            <div class="col-12 sx-order-result-block <?php echo $controller->order->weight > 0 ? "" : "sx-hidden"; ?>">
                                <div class="float-right sx-weight" data-value="<?= (float)$controller->order->weight; ?>">
                                    <?= $controller->order->weightFormatted; ?>
                                </div>
                                <div class="pull-left">Вес</div>
                            </div>
                        </div>

                    </div>
                    <div class="sx-checkout-btn-wrapper sx-lock">
                        <div color="#0EA432" class="sx-checkout-menu-trigger">
                            <i class="fas icon fa-ellipsis-v fa-fw" style="color: white; font-size: 23px;"></i>
                        </div>

                        <div class="sx-checkout-menu sx-closed">
                            <!--<div class="sc-dxgOiQ hEwctr"><i class="fa icon fa-history fa-fw"></i>Отложить чек</div>-->
                            <div color="systemRed" class="item sx-clear-order-items"><i class="fa icon fa-times fa-fw" style="margin-right: 5px;"></i>Отменить чек</div>
                        </div>

                        <a tabindex="999" id="create-sale" color="#0EA432" class="sx-checkout-btn">
                            <div>
                                <div class="pull-left">
                                    <div class="sx-create-sale-text sx-order-type-text" data-sale="Продажа" data-return="Возврат">
                                        Продажа
                                    </div>
                                </div>
                                <div class="pull-right"><b class="sx-money" data-value="<?= (float)$controller->order->money->amount; ?>"><?php echo $controller->order->money; ?></b></div>
                            </div>
                        </a>

                    </div>
                </div>

            </div>


            <?php if (!$controller->shift) : ?>
                <div class="ClosedShift-wrapper"><a class="sc-iQNlJl dCkSgp">
                        <i class="fa icon fa-arrow left fa-fw"></i></a>
                    <div class="ClosedShift">
                        <div class="text-center" style="padding: 20px 0px;"><i class="fa icon fa-lock fa-fw"></i></div>
                        <div>
                            <button class="ui green big button">Открыть смену</button>
                        </div>
                    </div>
                </div>

                <?php \yii\bootstrap\Modal::begin([
                    'id'           => 'sx-shift-create',
                    'header'       => 'Открытие смены',
                    'toggleButton' => false,
                ]); ?>

                <?
                $cacheBoxes = \Yii::$app->shop->backendShopStore->getShopCasheboxes()
                        ->innerJoinWith("shopCashebox2users as shopCashebox2users")
                        ->andWhere(['shopCashebox2users.cms_user_id' => \Yii::$app->user->id])
                        ->active()
                        ->all()
                ;
                ?>

                <?php if ($cacheBoxes) : ?>
                    <?php $form = \skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
                        'action'                 => \yii\helpers\Url::to(['create-shift']),
                        'enableAjaxValidation'   => false,
                        'enableClientValidation' => false,
                        'clientCallback'         => new \yii\web\JsExpression(<<<JS
    function (ActiveFormAjaxSubmit) {
        
        
            
        ActiveFormAjaxSubmit.on('success', function(e, response) {

            ActiveFormAjaxSubmit.AjaxQueryHandler.set("allowResponseSuccessMessage", false);
            ActiveFormAjaxSubmit.AjaxQueryHandler.set("allowResponseErrorMessage", false);
            
            if (response.message) {
                $(".sx-success-result").empty().append("<div class='sx-message'>✓ " + response.message + "</div>");
            }
            
            setTimeout(function() {
                window.location.reload();
            }, 1000);
            
            
        });
        
        ActiveFormAjaxSubmit.on('error', function(e, response) {
            ActiveFormAjaxSubmit.AjaxQueryHandler.set("allowResponseSuccessMessage", false);
            ActiveFormAjaxSubmit.AjaxQueryHandler.set("allowResponseErrorMessage", false);
            
            console.log("error");
            $(".error-summary").empty().append("<p>" +  response.message + "</li>");
            $(".error-summary").show();
        });
    }
JS
                        ),
                    ]); ?>
                    <?php
                    $shopCasheboxShift = new \skeeks\cms\shop\models\ShopCasheboxShift();
                    echo $form->field($shopCasheboxShift, "shop_cashebox_id")->label("Касса")->widget(
                        \skeeks\cms\widgets\Select::class, [
                            'items' => \yii\helpers\ArrayHelper::map($cacheBoxes, 'id', 'name'),
                        ]
                    );
                    ?>
                    <div class="d-flex sx-submit-wrapper">
                        <div class="sx-success-result my-auto" style="width: 100%;"></div>
                        <div class="sx-btns d-flex">
                            <button class="ui large primary button" type="submit">Открыть смену</button>
                        </div>

                    </div>


                    <?php echo $form->errorSummary($shopCasheboxShift) ?>
                    <?php $form::end(); ?>
                <?php else : ?>
                    <h2>В магазине не заведены кассы</h2>
                <?php endif; ?>


                <?php \yii\bootstrap\Modal::end(); ?>


            <?php endif; ?>
        </div>
    </div>
</div>

<div style="display: none;">

    <div class="sx-no-order-items-tmpl">
        <div>
            <div>Выберите товары</div>
            <img src="<?php echo \skeeks\cms\shop\cashier\assets\CashierAsset::getAssetUrl("img/arrow.svg"); ?>" alt=""></div>
    </div>

    <div class="products-sale-list-tmpl">
        <table>
            <thead>
            <tr>
                <th class="text-left">Наименование</th>
                <th class="text-right">Цена</th>
                <th class="text-right">Количество</th>
                <th class="text-right">Скидка</th>
                <th class="text-right">Итог</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <tr class="products-sale-list-item-tmpl">
                <td class="text-left sx-name"></td>
                <td class="sx-price"></td>
                <td class="input sx-quantity">
                    <input pattern="[0-9]+([\.,][0-9]+)?" type="text" class="text-right" value="1">
                </td>
                <td class="sx-discount">0%</td>
                <td class="sx-total"></td>
                <td class="delete sx-delete-order-item"><i class="far fa-trash-alt" style="color: rgb(255, 59, 48);"></i></td>
            </tr>
            </tbody>
        </table>
    </div>


</div>


<div class="portal">
    <div id="sx-create-order-success-modal" class="sx-modal-overlay sx-modal-overlay-fullscreen">
        <div class="sx-modal fullscreen custom-modal">
            <button class="ui huge basic button sx-close-modal">Закрыть</button>
            <div class="content">
                <div class="sx-inner-content">
                    <div>
                        <h1>Продажа прошла успешно!</h1>
                        <div class="sx-check-content">
                            Тут данные по чеку!
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="portal">
    <div id="sx-check-wait-modal" class="sx-modal-overlay sx-modal-overlay-fullscreen">
        <div class="sx-modal fullscreen custom-modal">
            <div class="content">
                <div class="sx-inner-content">
                    <div>
                        <h1>Идет получение чека....</h1>
                        <div class="sx-content">Ожидайте в данный момент идет обращение к кассе для получения чека.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="portal">
    <div id="sx-check-error-status" class="sx-modal-overlay sx-modal-overlay-fullscreen">
        <div class="sx-modal fullscreen custom-modal">
            <button class="ui huge basic button sx-close-modal">Закрыть</button>
            <div class="content">
                <div class="sx-inner-content">
                    <div>
                        <h1>Ошибка получения чека....</h1>
                        <div class="error-summary">Обратите к разработчикам.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="portal">
    <div id="sx-order-item-edit" class="sx-modal-overlay">
        <div class="sx-modal tiny">
            <div class="content">
                <div class="sx-inner-content">
                    <div>
                        Загрузка...
                    </div>
                    <button class="ui huge basic button sx-close-modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="portal">
    <div id="sx-final-modal" class="sx-modal-overlay sx-modal-overlay-fullscreen">
        <div class="sx-modal fullscreen custom-modal">
            <button class="ui huge basic button sx-close-modal">Закрыть</button>
            <div class="content">
                <div class="sx-inner-content">
                    <div>
                        <div><h1>Платежи</h1>
                            <div class="payments">
                                <div>
                                    <span>Итог</span>
                                    <span class="sx-money" data-value="<?= (float)$controller->order->money->amount; ?>"><?= $controller->order->money; ?></span>
                                </div>
                                <div><span>Принято</span><span>-</span></div>
                                <div class="disabled cash"><span>Наличные</span>
                                    <div><span>-</span></div>
                                </div>
                                <div class="disabled terminal"><span>Банковская карта</span>
                                    <div><span>-</span></div>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top: 80px;">
                            <textarea placeholder="Комментарий к продаже" class="sx-order-comment" id="sx-order-comment" style="height: 8em;"></textarea></div>
                    </div>
                    <div>
                        <div class="sx-pre-order">
                            <h1 class="sx-order-type-text" data-return="Вернуть деньги" data-sale="Принять оплату">Принять оплату</h1>
                            <div class="ui huge basic fluid buttons" id="sx-payment-type" style="margin-top: 12px;">
                                <button class="ui active button" data-type="<?php echo \skeeks\cms\shop\models\ShopPayment::STORE_PAYMENT_TYPE_CASH; ?>">Наличными</button>
                                <button class="ui button" data-type="<?php echo \skeeks\cms\shop\models\ShopPayment::STORE_PAYMENT_TYPE_CARD; ?>">Банковской картой</button>
                            </div>

                            <?php if($controller->shift && $controller->shift->shopCashebox->shopCloudkassa) : ?>


                                <div class="ui huge basic fluid buttons" id="sx-is-print">
                                    <button class="ui active button" data-value="1">Печатать чек</button>
                                    <button class="ui button" data-value="0">Чек онлайн</button>

                                    <?php if(\Yii::$app->shop->backendShopStore->is_allow_no_check) : ?>
                                        <button class="ui button" data-value="2">Без чека</button>
                                    <?php endif; ?>

                                </div>
                            <?php else : ?>
                                <div style="display: none;">
                                    <div class="ui huge basic fluid buttons" id="sx-is-print">
                                        <button class="ui button active" data-value="0">Нет</button>
                                    </div>
                                </div>
                            <?php endif; ?>


                            <!--<div class="sc-dfVpRl bMKUXX">
                                <input id="sum-input" type="number" placeholder="0.00 ₽" class="sc-gzOgki cSSjPU" value="0">
                                <span>Сумма</span>
                            </div>-->
                            <div style="margin-top: 15px;">
                                <div class="ui huge fluid primary button sx-create-order sx-btn-hover" role="button" tabindex="0">
                                    <span class="sx-order-type-text" data-return="Вернуть" data-sale="Принять">Принять</span>
                                    <span class="sx-money" data-value="<?= (float)$controller->order->money->amount; ?>"><?= $controller->order->money; ?></span>
                                </div>
                            </div>

                            <div class="sx-create-order-errors-block error-summary sx-hidden"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($controller->shift) : ?>
    <?php \yii\bootstrap\Modal::begin([
        'id'           => 'sx-shift-close',
        'header'       => 'Закрытие смены',
        'toggleButton' => false,
    ]); ?>


    <p>Вы действительно хотите закрыть смену?</p>
    <div class="d-flex">

        <div class="sx-btns d-flex">
            <button class="ui large primary button sx-close-shift-btn-submit" type="submit">Закрыть смену</button>
        </div>
    </div>


    <?php \yii\bootstrap\Modal::end(); ?>


    <?php /*\yii\bootstrap\Modal::begin([
        'id'           => 'sx-create-order-success',
        'header'       => 'Успешная продажа',
        'toggleButton' => false,
    ]); */?><!--


    <p>Продажа прошла успешно!</p>
    <div class="d-flex">
        <div class="d-flex">
            <button class="ui large primary button sx-close-standart-modal">Закрыть</button>
        </div>
    </div>
    <?php /*\yii\bootstrap\Modal::end(); */?>


    <?php /*\yii\bootstrap\Modal::begin([
        'id'           => 'sx-wait-check',
        'header'       => 'Ожидание чека',
        'toggleButton' => false,
        'closeButton'  => false,
    ]); */?>


    <div class="sx-check-wait">
        <div class="">Идет обращение к кассе.</div>
        <div class="">Не закрывайте это окно.</div>
        <div class=""></div>
    </div>

    --><?php /*\yii\bootstrap\Modal::end(); */?>


<?php endif; ?>

