<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 05.08.2015
 */

namespace skeeks\cms\shop\grid;

use yii\grid\DataColumn;

/**
 * Class BasketNameGridColumn
 * @package skeeks\cms\shop\grid
 */
class BasketSumGridColumn extends DataColumn
{
    public $attribute = "price";
    public $format = "raw";

    /**
     * @param mixed $model
     * @param mixed $key
     * @param int   $index
     * @return string
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        if (!$this->label) {
            $this->label = \Yii::t('skeeks/shop/app', 'Sum');
        }

        return (string)$model->money->multiply($model->quantity);
    }
}