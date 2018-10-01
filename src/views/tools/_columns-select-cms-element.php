<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 18.06.2015
 */
return [

    [
        'class'  => \yii\grid\DataColumn::class,
        'value'  => function ($model) {
            $shopProduct = \skeeks\cms\shop\models\ShopProduct::getInstanceByContentElement($model);
            $basePrice = $shopProduct->baseProductPrice;


            return \yii\helpers\Html::a('<i class="glyphicon glyphicon-circle-arrow-left"></i> '.\Yii::t('app',
                    'Choose'), $model->id, [
                'class'     => 'btn btn-primary sx-row-action',
                'onclick'   => 'sx.SelectCmsElement.submit('.\yii\helpers\Json::encode(array_merge($model->toArray(), [
                        'url'           => $model->url,
                        'basePrice'     => $basePrice,
                        'basePriceType' => $basePrice->typePrice,
                        'product'       => $shopProduct,
                        'measure'       => $shopProduct->measure,
                    ])).'); return false;',
                'data-pjax' => 0,
            ]);
        },
        'format' => 'raw',
    ],


    [
        'class'     => \yii\grid\DataColumn::class,
        'value'     => function (\skeeks\cms\models\CmsContentElement $model) {
            return $model->cmsContent->name;
        },
        'format'    => 'raw',
        'attribute' => 'content_id',
        'filter'    => \Yii::$app->shop->getArrayForSelectElement(),
    ],


    [
        'class' => \skeeks\cms\grid\ImageColumn2::class,
    ],

    'name',
    ['class' => \skeeks\cms\grid\CreatedAtColumn::class],
    ///['class' => \skeeks\cms\grid\UpdatedAtColumn::class],
    ///['class' => \skeeks\cms\grid\PublishedAtColumn::class],
    /*[
        'class' => \skeeks\cms\grid\DateTimeColumnData::class,
        'attribute' => "published_to",
    ],*/

    //['class' => \skeeks\cms\grid\CreatedByColumn::class],
    //['class' => \skeeks\cms\grid\UpdatedByColumn::class],

    [
        'class'     => \yii\grid\DataColumn::class,
        'value'     => function (\skeeks\cms\models\CmsContentElement $model) {
            if (!$model->cmsTree) {
                return null;
            }

            $path = [];

            if ($model->cmsTree->parents) {
                foreach ($model->cmsTree->parents as $parent) {
                    if ($parent->isRoot()) {
                        $path[] = "[".$parent->site->name."] ".$parent->name;
                    } else {
                        $path[] = $parent->name;
                    }
                }
            }
            $path = implode(" / ", $path);
            return "<small><a href='{$model->cmsTree->url}' target='_blank' data-pjax='0'>{$path} / {$model->cmsTree->name}</a></small>";
        },
        'format'    => 'raw',
        'filter'    => \skeeks\cms\helpers\TreeOptions::getAllMultiOptions(),
        'attribute' => 'tree_id',
    ],

    [
        'class'  => \yii\grid\DataColumn::class,
        'value'  => function (\skeeks\cms\models\CmsContentElement $model) {
            $result = [];

            if ($model->cmsContentElementTrees) {
                foreach ($model->cmsContentElementTrees as $contentElementTree) {

                    $site = $contentElementTree->tree->root->site;
                    $result[] = "<small><a href='{$contentElementTree->tree->url}' target='_blank' data-pjax='0'>[{$site->name}]/.../{$contentElementTree->tree->name}</a></small>";

                }
            }

            return implode('<br />', $result);

        },
        'format' => 'raw',
        'label'  => \Yii::t('app', 'Additional sections'),
    ],

    [
        'label' => \skeeks\cms\shop\Module::t('app', 'Base price'),
        'class' => \yii\grid\DataColumn::class,
        'value' => function (\skeeks\cms\models\CmsContentElement $model) {
            $shopProduct = \skeeks\cms\shop\models\ShopProduct::getInstanceByContentElement($model);
            if ($shopProduct) {
                return (string)$shopProduct->baseProductPrice->money;
            }

            return null;
        },
    ],

    [
        'attribute' => 'active',
        'class'     => \skeeks\cms\grid\BooleanColumn::class,
    ],

    [
        'class'  => \yii\grid\DataColumn::class,
        'value'  => function (\skeeks\cms\models\CmsContentElement $model) {

            return \yii\helpers\Html::a('<i class="glyphicon glyphicon-arrow-right"></i>', $model->absoluteUrl, [
                'target'    => '_blank',
                'title'     => \Yii::t('app', 'Watch to site (opens new window)'),
                'data-pjax' => '0',
                'class'     => 'btn btn-default btn-sm',
            ]);

        },
        'format' => 'raw',
    ],
]
?>


