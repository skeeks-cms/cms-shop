<?php
/* @var $model \skeeks\cms\models\CmsUser */
/* @var $this yii\web\View */
/* @var $controller \skeeks\cms\controllers\AdminCmsContentElementController
 * /* @var $action \skeeks\cms\backend\actions\BackendModelCreateAction|\skeeks\cms\backend\actions\IHasActiveForm
 */
/* @var $model \common\models\User */
$controller = $this->context;
$action = $controller->action;
$content = $controller->content;
$dm = new \skeeks\cms\base\DynamicModel([
    'from',
    'to',
]);
$dm->addRule(['from', 'to'], 'string');
$dm->load(\Yii::$app->request->get());

$q = \skeeks\cms\models\CmsContentElement::find()->from([
    'c' => \skeeks\cms\models\CmsContentElement::tableName(),
])
    ->cmsSite()
    //->joinWith('createdBy as createdBy')
    ->andWhere(['c.content_id' => $content->id])
    ->groupBy(['c.created_by'])
    ->select([
        'count'      => new \yii\db\Expression("count(1)"),
        "created_by" => 'c.created_by',
    ]);


$qProducts = \skeeks\cms\shop\models\ShopStoreProduct::find()
    ->from([
        'ssp' => \skeeks\cms\shop\models\ShopStoreProduct::tableName(),
    ])
    ->joinWith('shopProduct as shopProduct')
    ->joinWith('shopProduct.cmsContentElement as cce')
    //->andWhere(['!=', 'shopProduct.created_by', new \yii\db\Expression("ssp.updated_by")])
    //->andWhere(['=', 'cce.cms_site_id', \Yii::$app->skeeks->site->id])
    //->groupBy(['ssp.updated_by'])
    ->select([
        'count'      => new \yii\db\Expression("count(1)"),
        //"main_cce_by" => 'ssp.updated_by',
        "shop_product_id" => 'ssp.shop_product_id',
        "id" => 'ssp.id',
        //"delta" => new \yii\db\Expression("abs(ssp.updated_at - shopProduct.updated_at)"),
    ])
    /*->andHaving([
        '>=', 'delta', 60
    ])*/
;


if ($dm->from) {
    $start = strtotime($dm->from." 00:00:00");
    $q->andWhere(['>=', 'c.created_at', $start]);
    $qProducts->andWhere(['>=', 'ssp.updated_at', $start]);
}
if ($dm->to) {
    $to = strtotime($dm->to." 23:59:59");
    $q->andWhere(['<=', 'c.created_at', $to]);
    $qProducts->andWhere(['<=', 'ssp.updated_at', $to]);
}
//print_r($qProducts->createCommand()->rawSql);die;

$all = $q
    ->asArray()
    ->all();

/*$allProducts = $qProducts
    ->asArray()
    ->indexBy('main_cce_by')
    ->all();*/



?>
<?php $form = \yii\widgets\ActiveForm::begin([
    'method' => 'get',
]); ?>
<div class="sx-bg-secondary">
<div class="row" style="padding: 15px; padding-bottom: 0px;">
    <div class="col">
        <?php echo $form->field($dm, 'from')->textInput(['type' => 'date'])->label("Начало периода"); ?>
    </div>
    <div class="col">
        <?php echo $form->field($dm, 'to')->textInput(['type' => 'date'])->label("Конец периода"); ?>
    </div>
    <div class="col my-auto">
        <button type="submit" class="btn btn-primary">Отправить</button>
    </div>
</div>
</div>
<?php $form::end(); ?>
<div class="row" style="margin-top: 20px;">
    <div class="col-12" style="max-width: 500px;">
        <h4>Статистика добавления:</h4>
        <table class="table table-bordered">
            <?php if ($all) : ?>
                <?php foreach ($all as $data) : ?>
                    <tr>
                        <td style="width: 400px;">
                            <?php $user = \skeeks\cms\models\CmsUser::findOne($data['created_by']); ?>
                            <?php if($user) : ?>
                                <? $widget = \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                                    'controllerId' => 'cms/admin-user',
                                    'modelId' => $user->id
                                ]); ?>
                                <div class="d-flex flex-row">
                                    <div class="my-auto" style="margin-right: 5px;">
                                        <img src='<?= $user->avatarSrc ? $user->avatarSrc : \skeeks\cms\helpers\Image::getCapSrc(); ?>' style='max-width: 25px; max-height: 25px; border-radius: 50%;'/>
                                    </div>
                                    <div class="my-auto">
                                        <div style="overflow: hidden; max-height: 40px; text-align: left;">
                                            <?= $user->shortDisplayName; ?>
                                        </div>
                                    </div>
                                </div>
                                <? $widget::end(); ?>
                            <?php else: ?>
                                не пользователь
                            <?php endif; ?>
                        </td>
                        <td>
                            <b><?php echo $data['count']; ?></b>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>
    <?php /*if ($allProducts && \Yii::$app->skeeks->site->is_default) : */?><!--
    <div class="col-12" style="max-width: 500px;">
        <h4>Статистика привязки:</h4>
        <table class="table table-bordered">
            
                <?php /*foreach ($allProducts as $data) : */?>
                    <tr>
                        <td style="width: 400px;">
                            <?php /*$user = \skeeks\cms\models\CmsUser::findOne($data['main_cce_by']); */?>
                            <?/* $widget = \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                                'controllerId' => 'cms/admin-user',
                                'modelId' => $user->id
                            ]); */?>
                            <div class="d-flex flex-row">
                                <div class="my-auto" style="margin-right: 5px;">
                                    <img src='<?/*= $user->avatarSrc ? $user->avatarSrc : \skeeks\cms\helpers\Image::getCapSrc(); */?>' style='max-width: 25px; max-height: 25px; border-radius: 50%;'/>
                                </div>
                                <div class="my-auto">
                                    <div style="overflow: hidden; max-height: 40px; text-align: left;">
                                        <?/*= $user->shortDisplayName; */?>
                                    </div>
                                </div>
                            </div>
                            <?/* $widget::end(); */?>
                        </td>
                        <td>
                            <b><?php /*echo $data['count']; */?></b>
                        </td>
                    </tr>
                <?php /*endforeach; */?>
            
        </table>
    </div>
    --><?php /*endif; */?>
</div>
