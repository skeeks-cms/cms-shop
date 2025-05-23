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
?>
<?php $widget = \yii\bootstrap\Alert::begin([
    'closeButton' => false,
    'options'     => [
        'class' => 'alert-default',
    ],
]); ?>

<p>
    <div class="dropdown">
      <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Подключить маркетплейс
      </button>
      <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
        <a class="dropdown-item" href="<?php echo \yii\helpers\Url::to(['add', 'marketplace' => \skeeks\cms\shop\models\ShopMarketplace::MARKETPLACE_WILDBERRIES]); ?>">Wildberries</a>
        <a class="dropdown-item" href="<?php echo \yii\helpers\Url::to(['add', 'marketplace' => \skeeks\cms\shop\models\ShopMarketplace::MARKETPLACE_OZON]); ?>">Ozon</a>
        <a class="dropdown-item" href="<?php echo \yii\helpers\Url::to(['add', 'marketplace' => \skeeks\cms\shop\models\ShopMarketplace::MARKETPLACE_YANDEX_MARKET]); ?>">Yandex market</a>
        <!--<a class="dropdown-item" href="<?php /*echo \yii\helpers\Url::to(['add', 'doc_type' => \skeeks\cms\shop\models\ShopStoreDocMove::DOCTYPE_INVENTORY]); */?>">Инвентаризация</a>-->
      </div>
    </div>
</p>

<?php $widget::end(); ?>