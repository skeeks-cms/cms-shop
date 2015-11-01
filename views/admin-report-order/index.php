<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (ÑêèêÑ)
 * @date 01.11.2015
 */
/* @var $this yii\web\View */
?>
<?= \skeeks\cms\modules\admin\widgets\GridView::widget([
    'dataProvider' => new \yii\data\ActiveDataProvider([
        'query' => \skeeks\cms\shop\models\ShopOrder::find(),
    ]),
    'columns' => [
      'id',
    ],
]); ?>

