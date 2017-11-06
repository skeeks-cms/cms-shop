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
class BasketSumGridColumn extends DataColumn
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
        if (!$this->label)
        {
            $this->label = \Yii::t('skeeks/shop/app', 'Sum');
        }

        return \Yii::$app->money->intlFormatter()->format($model->money->multiply($model->quantity));
    }
}