<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/**
 * @var $this yii\web\View
 * @see http://jsfiddle.net/hashem/CrSpu/557/
 */

?>


<div class="sx-import-wrapper">
    <div class="row sx-info">
        <div class="col-12">
            <?
            $alert = \yii\bootstrap\Alert::begin([
                'closeButton' => false,
                'options'     => [
                    'class' => 'alert-default',
                ],
            ]);
            ?>
            Скопируйте товары в вашей таблице, кликните на ячейку и нажмите Ctrl+V - товары вставятся. Далее назовите столбцы и нажмите «Импортировать»
            <?
            $alert::end();
            ?>
            <div class="text-left">
                <div style="max-width: 724px;">
                    <img src="<?php echo \skeeks\cms\shop\assets\ShopStoreImportCopyAsset::getAssetUrl("ctrl-c.jpg"); ?>" class="img-fluid"/>
                </div>
                <h2>Как импортировать данные?</h2>
                <ol>
                    <li>Выделите данные в любой электронной таблице (Excel, Google таблицы, любая таблица)</li>
                    <li>Скопируйте их через сочетание клавиш Ctrl+C или через меню «Правка»</li>
                    <li>Вставьте в импорт при помощи Ctrl+V</li>
                    <li>Обязательно выберите каждому столбцу назначение (заголовок) через выпадающий список</li>
                    <li>Нажмите кнопку “Импортировать”</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="sx-import-values-wrapper">


        <div class="row" style="margin-bottom: 10px;">
            <div class="col-6">
                <div class="sx-bg-secondary sx-import-form-wrapper">
                    <ol style="color: gray; font-size: 12px; padding-left: 15px;">
                        <li>Выберите соответствие колонок вашей таблицы и данных сайта.</li>
                        <li>Удалите лишние строки данных</li>
                        <li>Нажмите кнопку "Запустить импорт"</li>
                    </ol>

                    <button class="btn btn-primary sx-start-import">Запустить импорт</button>
                </div>
            </div>
            <div class="col-6">
                <div class="sx-bg-secondary sx-import-form-wrapper">

                    <div class="sx-progress-tasks" id="sx-progress-tasks" style="display: none;">
                        <span style="vertical-align:middle;"><h3>Процесс импорта: <span class="sx-executing-ptc"></span>%</h3></span>
                        <span style="vertical-align:middle;"><span class="sx-executing-task-name"></span></span>
                        <div>
                            <div class="progress progress-striped active">
                                <div class="progress-bar progress-bar-success"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>


        <div class="sx-max-width-100">
            <div class="sx-max-height-100">
                <div class="sx-import-table-wrapper">
                </div>
            </div>
        </div>


    </div>
    <div style="opacity: 0;
        position: fixed;
        left: -100000px;
        top: -100000px;">
        <select id="sx-base-matches">
            <option value="">- выбрать -</option>
            <option value="name">Название</option>
            <option value="external_id">Уникальный код поставщика</option>
            <option value="purchase_price">Закупочная цена</option>
            <option value="selling_price">Розничная цена</option>
            <option value="quantity">Количество</option>
        </select>
        <textarea id="sx-source" rows="10"></textarea>
    </div>
</div>

<?
\skeeks\cms\shop\assets\ShopStoreImportCopyAsset::register($this);
$url = \yii\helpers\Url::to(['import-row']);
$this->registerJs(<<<JS
(function(sx, $, _)
{
    new sx.classes.Import({
        'backend_element': '{$url}',
        'required_matches' : [
            'external_id'
        ]
    });
})(sx, sx.$, sx._);
JS
);
?>

