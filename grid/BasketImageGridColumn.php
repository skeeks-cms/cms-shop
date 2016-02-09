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
class BasketImageGridColumn extends DataColumn
{
    public $format      = "raw";

    /**
     * @param \skeeks\cms\shop\models\ShopBasket $model
     * @param mixed $key
     * @param int $index
     * @return null|string
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $widget = new \skeeks\cms\modules\admin\widgets\AdminImagePreviewWidget([
            'image' => $model->product->cmsContentElement->image
        ]);

        return $widget->run();
    }
}