<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 06.03.2016
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsContentElement;

/**
 * @property ShopProduct $shopProduct
 * @property ShopViewedProduct[] $shopViewedProducts
 *
 * @property ShopCmsContentElement $parentContentElement
 * @property ShopCmsContentElement[] $childrenContentElements
 *
 * @property ShopCmsContentElement[] $tradeOffers
 *
 * Class ShopCmsContentElement
 * @package skeeks\cms\shop\models
 */
class ShopCmsContentElement extends CmsContentElement
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopProduct()
    {
        return $this->hasOne(ShopProduct::className(), ['id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopViewedProducts()
    {
        return $this->hasMany(ShopViewedProduct::className(), ['shop_product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTradeOffers()
    {
        return $this->hasMany(static::className(), ['parent_content_element_id' => 'id'])->orderBy(['priority' => SORT_ASC]);
    }
}