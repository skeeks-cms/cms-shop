<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/**
 * @var $this yii\web\View
 * @var $statuses \skeeks\cms\shop\models\ShopOrderStatus[]
 */
$q = \skeeks\cms\shop\models\ShopOrder::find()->isCreated()->andWhere(['cms_user_id' => \Yii::$app->user->id])->orderBy(['id' => SORT_DESC]);

$qStatuses = clone $q;
$qStatuses->select(['shop_order_status_id'])->groupBy(['shop_order_status_id']);

$statuses = \skeeks\cms\shop\models\ShopOrderStatus::find()->andWhere(['id' => $qStatuses])->orderBy(['priority' => SORT_ASC])->all();



$status = \Yii::$app->request->get("status", 'all');
if ($status != 'all') {
    $q->andWhere(['shop_order_status_id' => $status]);
}

$dataProvider = new \yii\data\ActiveDataProvider([
    'query' => $q
]);

?>
<h1>Мои заказы</h1>
<div>

    <?php if($statuses > 1) : ?>
        <div class="sx-fast-filters" style="margin-bottom: 1rem;">
            <div class="row">
                <div class="col-12 col-sm"><a class="btn <?php echo $status == 'all' ? "btn-primary" : "btn-default" ?> btn-block" href="<?php echo \yii\helpers\Url::to(['index']); ?>">Все</a></div>
                <?php foreach($statuses as $orderStatus) : ?>
                    <div class="col-12 col-sm"><a class="btn <?php echo $status == $orderStatus->id ? "btn-primary" : "btn-default" ?>  btn-block" href="<?php echo \yii\helpers\Url::to(['index', 'status' => $orderStatus->id]); ?>"><?php echo $orderStatus->name; ?></a></div>
                <?php endforeach; ?>
                
            </div>
        </div>
    <?php endif; ?>
    
    

    <?
    echo \yii\widgets\ListView::widget([
        'dataProvider' => $dataProvider,
        'itemView'     => '_order-item',
        'emptyText'    => 'У вас еще нет заказов',
        'options'      => [],
            /*[
                'tag'   => 'div',
                'class' => 'sx-item-list',
            ],*/
        'itemOptions'  => [
            'tag' => false,
        ],
        'layout'       => '
    <div class="row sx-item-list list-view">{items}</div>
    <div class="row"><div class="col-md-12">{pager}</div></div>',
        'pager'        => [
            'class' => \skeeks\cms\themes\unify\widgets\ScrollAndSpPager::class,
        ],
    ]);
    ?>
    
</div>
