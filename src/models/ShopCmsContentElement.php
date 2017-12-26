<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 06.03.2016
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsContentElement;

/**
 * @property ShopProduct $shopProduct
 * @property ShopViewedProduct[] $shopViewedProducts
 *
 * @property ShopCmsContentElement $parentContentElement
 * @property ShopCmsContentElement[] $childrenContentElements
 *
 * @property ShopCmsContentElement[] $tradeOffers
 * @property ShopContent $shopContent
 *
 * Class ShopCmsContentElement
 * @package skeeks\cms\shop\models
 */
class ShopCmsContentElement extends CmsContentElement
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->on(self::EVENT_BEFORE_DELETE, [$this, "_deleteShops"]);
        $this->on(self::EVENT_AFTER_DELETE, [$this, "_updateParentAfterDelete"]);
    }

    //Удалить все что связано с элементом
    public function _deleteShops()
    {
        if ($this->shopProduct) {
            $this->shopProduct->delete();
        }

        if ($this->tradeOffers) {
            foreach ($this->tradeOffers as $tradeOffer) {
                $tradeOffer->delete();
            }
        }
    }

    /**
     * Проверка родителя если он есть, и изменение его типа если нужно после удаления текущего
     * @param $event
     */
    public function _updateParentAfterDelete($event)
    {
        //Если есть родительский элемент
        if ($this->parent_content_element_id) {
            if ($offers = $this->parentContentElement->getTradeOffers()->all()) {
                /**
                 * Если есть оферы, берем одного из них и обновляем цены, это повлечет за собой обновление цены у продукта
                 * @var $offer ShopCmsContentElement
                 */
                $offer = array_shift($offers);

                if ($offer->shopProduct && $offer->shopProduct->shopProductPrices) {
                    foreach ($offer->shopProduct->shopProductPrices as $shopPrice) {
                        $shopPrice->save();
                    }
                }

                $this->parentContentElement->shopProduct->product_type = ShopProduct::TYPE_OFFERS;
            } else {
                $this->parentContentElement->shopProduct->product_type = ShopProduct::TYPE_SIMPLE;
            }

            $this->parentContentElement->shopProduct->save();
        }
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopProduct()
    {
        return $this->hasOne(ShopProduct::className(), ['id' => 'id'])->from(['shopProduct' => ShopProduct::tableName()]);
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
    public function getShopContent()
    {
        return $this->hasOne(ShopContent::className(), ['content_id' => 'content_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTradeOffers()
    {
        $childContentId = null;
        if ($this->shopContent) {
            $childContentId = $this->shopContent->children_content_id;
        }

        return $this
            ->hasMany(static::className(), ['parent_content_element_id' => 'id'])
            ->andWhere(["content_id" => $childContentId])
            ->orderBy(['priority' => SORT_ASC]);
    }
}