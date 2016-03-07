<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 18.06.2015
 * @var $model \skeeks\cms\shop\models\ShopCmsContentElement
 */

$columns = [


    [
        'class' => \skeeks\cms\grid\ImageColumn2::className(),
    ],

    'name',
    //['class' => \skeeks\cms\grid\CreatedAtColumn::className()],
    ['class' => \skeeks\cms\grid\UpdatedAtColumn::className()],
    //['class' => \skeeks\cms\grid\PublishedAtColumn::className()],
    //[
    //    'class' => \skeeks\cms\grid\DateTimeColumnData::className(),
    //    'attribute' => "published_to"
    //],

    //['class' => \skeeks\cms\grid\CreatedByColumn::className()],
    //['class' => \skeeks\cms\grid\UpdatedByColumn::className()],

    [
        'class'     => \yii\grid\DataColumn::className(),
        'value'     => function(\skeeks\cms\models\CmsContentElement $model)
        {
            if (!$model->cmsTree)
            {
                return null;
            }

            $path = [];

            if ($model->cmsTree->parents)
            {
                foreach ($model->cmsTree->parents as $parent)
                {
                    if ($parent->isRoot())
                    {
                        $path[] =  "[" . $parent->site->name . "] " . $parent->name;
                    } else
                    {
                        $path[] =  $parent->name;
                    }
                }
            }
            $path = implode(" / ", $path);
            return "<small><a href='{$model->cmsTree->url}' target='_blank' data-pjax='0'>{$path} / {$model->cmsTree->name}</a></small>";
        },
        'format'    => 'raw',
        'filter' => \skeeks\cms\helpers\TreeOptions::getAllMultiOptions(),
        'attribute' => 'tree_id'
    ],

    [
        'class'     => \yii\grid\DataColumn::className(),
        'value'     => function(\skeeks\cms\models\CmsContentElement $model)
        {
            $result = [];

            if ($model->cmsContentElementTrees)
            {
                foreach ($model->cmsContentElementTrees as $contentElementTree)
                {
                    $site = $contentElementTree->tree->root->site;
                    $result[] = "<small><a href='{$contentElementTree->tree->url}' target='_blank' data-pjax='0'>[{$site->name}]/.../{$contentElementTree->tree->name}</a></small>";

                }
            }

            return implode('<br />', $result);

        },
        'format' => 'raw',
        'label' => \skeeks\cms\shop\Module::t('app', 'Advanced Topics'),
    ],

    [
        'attribute' => 'active',
        'class' => \skeeks\cms\grid\BooleanColumn::className()
    ],


    [
        'label' => \skeeks\cms\shop\Module::t('app', 'Base price'),
        'class' => \yii\grid\DataColumn::className(),
        'value' => function(\skeeks\cms\models\CmsContentElement $model)
        {
            $shopProduct = \skeeks\cms\shop\models\ShopProduct::getInstanceByContentElement($model);
            if ($shopProduct)
            {
                return \Yii::$app->money->intlFormatter()->format($shopProduct->baseProductPrice->money);
            }

            return null;
        }
    ],

    [
        'class'     => \yii\grid\DataColumn::className(),
        'value'     => function(\skeeks\cms\models\CmsContentElement $model)
        {

            return \yii\helpers\Html::a('<i class="glyphicon glyphicon-arrow-right"></i>', $model->absoluteUrl, [
                'target' => '_blank',
                'title' => \skeeks\cms\shop\Module::t('app', 'View online (opens new window)'),
                'data-pjax' => '0',
                'class' => 'btn btn-default btn-sm'
            ]);

        },
        'format' => 'raw'
    ]
];

$typeColumn = //TODO: показывать только для контента с предложениями
[
    'class'     => \yii\grid\DataColumn::className(),
    'label'     => 'Тип товара',
    'value'     => function(\skeeks\cms\shop\models\ShopCmsContentElement $shopCmsContentElement)
    {
        if ($shopCmsContentElement->shopProduct)
        {
            return \yii\helpers\ArrayHelper::getValue(\skeeks\cms\shop\models\ShopProduct::possibleProductTypes(), $shopCmsContentElement->shopProduct->product_type);
        }
    }
];
if ($model->cmsContent)
{
    /**
     * @var $shopContent \skeeks\cms\shop\models\ShopContent
     */
    $shopContent = \skeeks\cms\shop\models\ShopContent::findOne(['content_id' => $model->cmsContent->id]);
    if ($shopContent)
    {
        if ($shopContent->childrenContent)
        {
            $columns = \yii\helpers\ArrayHelper::merge([$typeColumn], $columns);
        }
    }

}
return $columns;
?>


