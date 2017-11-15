<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 08.10.2015
 */

namespace skeeks\cms\shop\widgets\admin;

use skeeks\cms\mail\helpers\Html;
use skeeks\cms\models\CmsUser;
use skeeks\cms\modules\admin\widgets\AdminImagePreviewWidget;
use skeeks\cms\shop\models\ShopProductPrice;
use yii\base\Widget;

/**
 * Class AdminBuyerUserWidget
 * @package skeeks\cms\shop\widgets
 */
class PropductPriceChangeAdminWidget extends Widget
{
    /**
     * @var ShopProductPrice
     */
    public $productPrice = null;

    /**
     * Подготовка данных для шаблона
     * @return $this
     */
    public function run()
    {
        return $this->render('product-price-change', [
            'widget' => $this
        ]);
    }


}
