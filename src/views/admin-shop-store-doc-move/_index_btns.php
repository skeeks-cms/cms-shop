<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/**
* @var $this yii\web\View
*/

$qStores = \skeeks\cms\shop\models\ShopStore::find()->isSupplier(false)->cmsSite()->select([\skeeks\cms\shop\models\ShopStore::tableName().'.id']);

$productsQuery = \skeeks\cms\shop\models\ShopStoreProduct::find()
    ->select([\skeeks\cms\shop\models\ShopStoreProduct::tableName().".*", 'total_quantity' => new \yii\db\Expression("if(sum(shopStoreProductMoves.quantity), sum(shopStoreProductMoves.quantity), 0)")])
    ->andWhere(['shop_store_id' => $qStores])
    ->andWhere(["!=", \skeeks\cms\shop\models\ShopStoreProduct::tableName().'.quantity', 0])
    ->andHaving([
        "!=", \skeeks\cms\shop\models\ShopStoreProduct::tableName().'.quantity', new \yii\db\Expression("total_quantity")
    ])
    ->joinWith("shopStoreProductMoves as shopStoreProductMoves")
    ->groupBy([\skeeks\cms\shop\models\ShopStoreProduct::tableName().".id"]);

$backendCorrectionUrl = \yii\helpers\Url::to(['create-correction']);
$this->registerJs(<<<JS

$(".sx-create-correction").on("click", function() {
    var jBtn = $(this);
    if (jBtn.hasClass("disabled")) {
        return false;
    }
    var Blocker = sx.block($(".sx-main-col"));
    jBtn.addClass("disabled");
    
    var AjaxQuery = sx.ajax.preparePostQuery("{$backendCorrectionUrl}");
    var AjaxHandler = new sx.classes.AjaxHandlerStandartRespose(AjaxQuery);
    
    AjaxHandler.on("success", function () {
        setTimeout(function() {
            sx.notify.info("Страница сейчас будет перезагружена");
        }, 1000)
        
        setTimeout(function() {
            window.location.reload();
        }, 1000)
    });
    AjaxHandler.on("error", function () {
        Blocker.unblock();
        jBtn.removeClass("disabled");
    });
    
    AjaxQuery.execute();
    
    return false;
});


JS
);
?>

<?php \yii\bootstrap\Alert::begin([
    'closeButton' => false,
    'options'     => [
        'class' => 'alert-default',
    ],
]); ?>
<?php $count = $qStores->count() ?>
<?php if ($count) : ?>

    <!--<p style="margin-bottom: 5px;">У вас магазинов: <b><?php /*echo $count; */?></b></p>-->

    <?php if ($productsQuery->count()) : ?>
        <p style="margin-bottom: 5px;">
            Есть расхождения по количеству и документам у товаров: <b><?php echo $productsQuery->count(); ?></b>
            <a href="#" class="btn btn-secondary sx-create-correction">Создать документ корректировку</a>
            <i class="far fa-question-circle" title="У некоторых из ваших товаров, уже было задано количество. Это количество не подтверждено никакими документами! Поэтому нажав на эту кнопку будет создан правильный документ корректировки по каждому из магазинов." data-toggle="tooltip" style="margin-left: 5px;"></i>
        </p>
    <?php endif; ?>
    <p>
        <div class="dropdown">
          <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Создать документ
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a class="dropdown-item" href="<?php echo \yii\helpers\Url::to(['add', 'doc_type' => \skeeks\cms\shop\models\ShopStoreDocMove::DOCTYPE_POSTING]); ?>">Оприходирование</a>
            <a class="dropdown-item" href="<?php echo \yii\helpers\Url::to(['add', 'doc_type' => \skeeks\cms\shop\models\ShopStoreDocMove::DOCTYPE_WRITEOFF]); ?>">Списание</a>
            <a class="dropdown-item" href="<?php echo \yii\helpers\Url::to(['add', 'doc_type' => \skeeks\cms\shop\models\ShopStoreDocMove::DOCTYPE_INVENTORY]); ?>">Инвентаризация</a>
          </div>
        </div>
    </p>
<?php else : ?>
    У вас нет магазинов. Для того чтобы использовать вдижение товаров создайте свой магазин в разделе "Настройки"
<?php endif; ?>


<?php \yii\bootstrap\Alert::end(); ?>