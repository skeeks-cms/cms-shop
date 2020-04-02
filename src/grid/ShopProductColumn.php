<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\grid;

use yii\grid\DataColumn;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopProductColumn extends DataColumn
{
    /**
     * @var string
     */
    public $attribute = "name";
    /**
     * @var string
     */
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
            $this->label = \Yii::t('skeeks/shop/app', 'Товар');
        }

        return \Yii::$app->view->render('@skeeks/cms/shop/grid/views/shop-product', [
            'model' => $model
        ]);
    }
}