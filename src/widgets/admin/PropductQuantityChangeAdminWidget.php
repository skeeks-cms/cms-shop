<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 08.10.2015
 */

namespace skeeks\cms\shop\widgets\admin;

use skeeks\cms\shop\models\ShopProduct;
use yii\base\Widget;

/**
 * Class AdminBuyerUserWidget
 * @package skeeks\cms\shop\widgets
 */
class PropductQuantityChangeAdminWidget extends Widget
{
    /**
     * @var ShopProduct
     */
    public $product = null;

    /**
     * Подготовка данных для шаблона
     * @return $this
     */
    public function run()
    {
        return $this->render('product-quantity-change', [
            'widget' => $this,
        ]);
    }


}
