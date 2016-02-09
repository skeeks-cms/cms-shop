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
class BasketNameGridColumn extends DataColumn
{
    public $attribute   = "name";
    public $format      = "raw";

    /**
     * @param mixed $model
     * @param mixed $key
     * @param int $index
     * @return string
     */
    protected function renderDataCellContent($model, $key, $index)
    {


        if ($model->product)
        {
            $content = Html::a($model->name, $model->product->cmsContentElement->url, [
                'target' => '_blank',
                'title' => "Смотреть на сайте (откроется в новом окне)",
                'data-pjax' => 0
            ]);

            if ($model->product->measure_ratio != 1)
            {
                $content .= <<<HTML
<p><small>Товар продается по: {$model->product->measure_ratio} {$model->product->measure->symbol_rus}</small></p>
HTML;
            }
            return $content;
        } else
        {
            return $model->name;
        }

        return null;
    }
}