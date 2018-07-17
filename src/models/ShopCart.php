<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\shop\helpers\ProductPriceHelper;
use yii\helpers\ArrayHelper;

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
        $ids = ArrayHelper::map($this->buyTypePrices, 'id', 'id');
        $minPh = null;
        
        if ($shopCmsContentElement->shopProduct->shopProductPrices) {
            foreach ($shopCmsContentElement->shopProduct->shopProductPrices as $price)
            {
                                    

                if (in_array($price->type_price_id, $ids)) {
                                    
                    $ph = new ProductPriceHelper([
                        'shopCmsContentElement' => $shopCmsContentElement,
                        'shopCart' => $this,
                        'price' => $price,
                    ]);
                    
                    if ($minPh === null) {
                        $minPh = $ph;
                        continue;
                    }
                    
                    
                    if ((float)$minPh->minMoney->amount == 0) {
                        $minPh = $ph;
                    } elseif ((float)$minPh->minMoney->amount > (float)$ph->minMoney->amount && (float)$ph->minMoney->amount > 0) {
                        $minPh = $ph;
                    }
                }
            }
        }
        
        return $minPh;
    }
}