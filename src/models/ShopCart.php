<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\shop\helpers\ProductPriceHelper;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class ShopCart extends ShopFuser
{
    /**
     * @param ShopCmsContentElement $shopCmsContentElement
     * @return ProductPriceHelper
     */
    public function getProductPriceHelper(ShopCmsContentElement $shopCmsContentElement)
    {
        return new ProductPriceHelper([
            'shopCmsContentElement' => $shopCmsContentElement,
            'shopCart' => $this,
        ]);
    }
}