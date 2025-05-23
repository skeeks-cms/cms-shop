<?php
/* @var $this yii\web\View */
/* @var $model \skeeks\cms\shop\models\ShopMarketplace */
/* @var $controller \skeeks\cms\backend\controllers\BackendModelController */
/* @var $action \skeeks\cms\backend\actions\BackendModelCreateAction|\skeeks\cms\backend\actions\IHasActiveForm */
$controller = $this->context;
$action = $controller->action;
$model = $action->model;

$this->render("view-css");
\skeeks\cms\backend\widgets\AjaxControllerActionsWidget::registerAssets();


?>
<?php if ($model->wbProvider) : ?>
<?php $apiResponseWarehouses = $model->wbProvider->methodContentWarehouses(); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="alert-default alert">
                <h1>Wildberries</h1>

                <div class="sx-properties-wrapper sx-columns-1">
                    <ul class="sx-properties sx-bg-secondary" style="padding: 10px;">
                        <li>
                            <span class="sx-properties--name">
                                Активность
                            </span>
                            <span class="sx-properties--value">
                                <?php echo $model->is_active ? '<span data-toggle="tooltip" title="Магазин включен"  style="color: green;">✓</span>' : '<span data-toggle="tooltip" title="Магазин деактивирован" style="color: red;">x</span>' ?>
                            </span>
                        </li>
                        <li>
                            <span class="sx-properties--name">
                                Ключ «Стандартный»
                                <i class="far fa-question-circle" style="margin-left: 5px;" data-toggle="tooltip"
                                   title="Основной ключ API для управления контентом">
                                </i>
                            </span>
                            <span class="sx-properties--value">
                                <?php if ($apiResponseWarehouses->isOk) : ?>
                                    <span style="color: green;">работает</span>
                                <?php else : ?>
                                    <span style="color: red;">не работает!</span>
                                <?php endif; ?>
                            </span>
                        </li>
                        <li>
                            <span class="sx-properties--name">
                                Ключ «Статистика»
                                <i class="far fa-question-circle" style="margin-left: 5px;" data-toggle="tooltip"
                                   title="Ключ для работы со статистическими данными">
                                </i>
                            </span>
                            <span class="sx-properties--value">
                                <?php $apiResponse = $model->wbProvider->methodStatSupplierIncomes([
                                    'dateFrom' => '2019-06-20'
                                ]); ?>
                                <?php if ($apiResponse->isOk) : ?>
                                    <span style="color: green;">работает</span>
                                <?php else : ?>
                                    <span style="color: red;">не работает!</span>
                                <?php endif; ?>
                            </span>
                        </li>
                        <li>
                            <span class="sx-properties--name">
                                Склады
                                <i class="far fa-question-circle" style="margin-left: 5px;" data-toggle="tooltip"
                                   title="Количество складов клиента на WB">
                                </i>
                            </span>
                            <span class="sx-properties--value">
                                <?php if ($apiResponseWarehouses->isOk) : ?>
                                    <?php echo count($apiResponseWarehouses->data); ?>
                                <?php else : ?>
                                    <span style="color: red;">api не работает!</span>
                                <?php endif; ?>
                            </span>
                        </li>
                        <li>
                            <span class="sx-properties--name">
                                Товары
                                <i class="far fa-question-circle" style="margin-left: 5px;" data-toggle="tooltip"
                                   title="Количество товаров на WB">
                                </i>
                            </span>
                            <span class="sx-properties--value">
                                <?php echo $model->getShopWbProducts()->count(); ?>
                            </span>
                        </li>

                        <?php $notProductLinks = $model->getShopWbProducts()->andWhere(['shop_product_id' => null])->count(); ?>
                        <?php if($notProductLinks) : ?>
                            <li>
                                <span class="sx-properties--name">
                                    Не связанные товары
                                    <i class="far fa-question-circle" style="margin-left: 5px;" data-toggle="tooltip"
                                       title="В магазине на маркетплейсе есть товары, которых нет на сайте.">
                                    </i>
                                </span>
                                <span class="sx-properties--value">
                                    <span style="color: red;"><?php echo $notProductLinks; ?></span>
                                </span>
                            </li>
                        <?php endif; ?>

                    </ul>
                </div>

                <?php /*if($apiResponse->isOk) : */?><!--
                    <pre>
                        <?php /*print_r($apiResponse->data); */?>
                    </pre>
                <?php /*else : */?>
                    <?php /*print_r($apiResponse->error_message); */?>
                    <?php /*print_r($apiResponse->content); */?>
                --><?php /*endif; */?>


            </div>
        </div>
    </div>
<?php endif ?>