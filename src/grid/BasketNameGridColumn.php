<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 05.08.2015
 */
namespace skeeks\cms\shop\grid;

use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\shop\models\ShopBasket;
use yii\grid\DataColumn;
use yii\helpers\Html;

/**
 * Class BasketNameGridColumn
 * @package skeeks\cms\shop\grid
 */
class BasketNameGridColumn extends DataColumn
{
    public $attribute   = "name";
    public $format      = "raw";

    /**
     * @param ShopBasket $model
     * @param mixed $key
     * @param int $index
     * @return string
     */
    protected function renderDataCellContent($model, $key, $index)
    {


        if ($model->url)
        {
            $content = Html::a($model->name, $model->url, [
                'target' => '_blank',
                'title' => \Yii::t('skeeks/shop/app','Watch Online (opens new window)'),
                'data-pjax' => 0
            ]);

            if ($model->product && $model->product->measure_ratio != 1)
            {
                $content .= <<<HTML
<p><small>Товар продается по: {$model->product->measure_ratio} {$model->product->measure->symbol_rus}</small></p>
HTML;
            }

            if ($model->product && $model->shopBasketProps)
            {
                $content .= "<p>";
                foreach ($model->shopBasketProps as $prop)
                {
                    $content .= <<<HTML
<small>{$prop->name}: {$prop->value}</small><br />
HTML;
                }
                $content .= "</p>";
            }

            return $content;
        } else
        {
            return $model->name;
        }

        return null;
    }
}