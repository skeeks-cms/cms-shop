<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */
/**
 * @var \skeeks\cms\shop\models\ShopCmsContentElement $model
 * @var \skeeks\cms\shop\models\ShopCmsContentElement $element
 */
$elementsByBarcode = \skeeks\cms\shop\models\ShopCmsContentElement::find()
    ->joinWith("shopProduct as shopProduct", true, "INNER JOIN")
    ->joinWith("cmsSite as cmsSite")
    ->cmsSite()
    ->andWhere([\skeeks\cms\shop\models\ShopCmsContentElement::tableName() . '.name' => $model->name])
;
/*print_r($elementsByBarcode->createCommand()->rawSql);die;*/

$elementsByBarcode = $elementsByBarcode->all();
?>

<? foreach ($elementsByBarcode as $element) : ?>
    <div class="row">
        <div class="col-12">
            <?php \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::begin([
                'controllerId' => "/shop/admin-cms-content-element",
                'modelId'      => $element->id,
                'tag'          => 'span',
                'options'      => [
                    'style' => 'text-align: left; margin-bottom: 3px;',
                    'class' => 'btn btn-default btn-xs',
                ],
            ]); ?>

            <?php echo $element->asText; ?>
            <?php \skeeks\cms\backend\widgets\AjaxControllerActionsWidget::end(); ?>
        </div>
    </div>
<? endforeach; ?>

