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
use yii\grid\DataColumn;
use yii\helpers\Html;

/**
 * Class BasketNameGridColumn
 * @package skeeks\cms\shop\grid
 */
class BasketPriceGridColumn extends DataColumn
{
    public $attribute   = "price";
    public $format      = "raw";

    /**
     * @param mixed $model
     * @param mixed $key
     * @param int $index
     * @return string
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $shopBasket = $model;
        if ($shopBasket->discount_value)
        {
            return "<span style='text-decoration: line-through;'>" . \Yii::$app->money->intlFormatter()->format($shopBasket->moneyOriginal) . "</span><br />". Html::tag('small', $shopBasket->notes) . "<br />" . \Yii::$app->money->intlFormatter()->format($shopBasket->money) . "<br />" . Html::tag('small', $shopBasket->discount_name . ": " . $shopBasket->discount_value);
        } else
        {
            return \Yii::$app->money->intlFormatter()->format($shopBasket->money) . "<br />" . Html::tag('small', $shopBasket->notes);
        }
    }
}