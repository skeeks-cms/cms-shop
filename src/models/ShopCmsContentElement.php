<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 06.03.2016
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\models\CmsContentElement;
use yii\helpers\ArrayHelper;

/**
 * @property ShopProduct             $shopProduct
 * @property ShopViewedProduct[]     $shopViewedProducts
 *
 * @property ShopCmsContentElement   $parentContentElement
 * @property ShopCmsContentElement[] $childrenContentElements
 *
 * @property ShopCmsContentElement[] $tradeOffers
 * @property ShopContent             $shopContent
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
    public function getTradeOffers()
    {
        $childContentId = null;
        if ($this->shopContent) {
            $childContentId = $this->shopContent->children_content_id;
        }

        return $this
            ->hasMany(static::class, ['parent_content_element_id' => 'id'])
            ->andWhere(["content_id" => $childContentId])
            ->orderBy(['priority' => SORT_ASC]);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopProduct()
    {
        return $this->hasOne(ShopProduct::class, ['id' => 'id'])->from(['shopProduct' => ShopProduct::tableName()]);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopViewedProducts()
    {
        return $this->hasMany(ShopViewedProduct::class, ['shop_product_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopContent()
    {
        return $this->hasOne(ShopContent::class, ['content_id' => 'content_id']);
    }
    /**
     * @return CmsContentElement|static
     */
    public function copy()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {

            $model = parent::copy();
            $sp = $this->shopProduct;

            ArrayHelper::remove($sp, 'id');
            ArrayHelper::remove($sp, 'created_at');
            ArrayHelper::remove($sp, 'created_by');
            ArrayHelper::remove($sp, 'updated_by');
            ArrayHelper::remove($sp, 'updated_at');
            ArrayHelper::remove($sp, 'product_id');

            $spNew = new ShopProduct(ArrayHelper::merge($sp->toArray(), [
                'id' => $model->id,
            ]));
            $spNew->save();


            /**
             * @var $prices ShopProductPrice[]
             */
            if ($prices = $sp->getShopProductPrices()->all()) {
                foreach ($prices as $price) {
                    $priceData = $price->toArray();

                    ArrayHelper::remove($priceData, 'id');
                    ArrayHelper::remove($priceData, 'created_at');
                    ArrayHelper::remove($priceData, 'created_by');
                    ArrayHelper::remove($priceData, 'updated_by');
                    ArrayHelper::remove($priceData, 'updated_at');
                    ArrayHelper::remove($priceData, 'product_id');

                    if (!$priceNew = ShopProductPrice::findOne(['product_id' => $model->id, 'type_price_id' => $price->type_price_id])) {
                        $priceNew = new ShopProductPrice(ArrayHelper::merge($priceData, [
                            'product_id' => $model->id,
                        ]));
                    } else {
                        $priceNew->setAttributes(ArrayHelper::merge($priceData, [
                            'product_id' => $model->id,
                        ]));
                    }

                    $priceNew->save();
                }
            }

            if ($sp->tradeOffers) {
                $spNew->product_type = ShopProduct::TYPE_OFFERS;
                $spNew->save();

                /**
                 * @var $offer ShopCmsContentElement
                 */
                foreach ($sp->tradeOffers as $offer) {
                    $newOffer = $offer->copy();
                    $newOffer->parent_content_element_id = $model->id;
                    $newOffer->save();


                }

                $spNew->product_type = ShopProduct::TYPE_OFFERS;
                $spNew->save();
            }

            $transaction->commit();


        } catch (\Exception $e) {

            $transaction->rollBack();
            throw $e;
        }


        return $model;

    }

    /**
     * @return string
     */
    public function asText()
    {
        $text = parent::asText();

        $result = [];
        //Если это предложение, то надо в заголовок добавить ключевые свойства.
        if ($this->shopProduct->isOfferProduct) {
            if (\Yii::$app->shop->offers_properties) {
                foreach (\Yii::$app->shop->offers_properties as $propertyCode)
                {
                    if ($value = $this->relatedPropertiesModel->getAttribute($propertyCode)) {
                        $result[] = $this->relatedPropertiesModel->getAttributeAsText($propertyCode);
                    }
                }
            }
        }

        if ($result) {
            $text .= " [" . implode(", ", $result) . "]";
        }

        return $text;
    }



    public function loadDataToMainModel(ShopCmsContentElement $model)
    {
        $model->name = $this->name;

        if ($this->shopProduct && $this->shopProduct->supplier_external_jsondata && $this->shopProduct->shopSupplier) {
            foreach ($this->shopProduct->supplier_external_jsondata as $key => $value)
            {
                /**
                 * @var $property ShopSupplierProperty
                 * @var $option ShopSupplierPropertyOption
                 */
                if ($property = $this->shopProduct->shopSupplier->getShopSupplierProperties()->andWhere(['external_code' => $key])->one()) {
                    if ($property->cmsContentProperty) {
                        $code = $property->cmsContentProperty->code;
                        if (in_array($property->property_type, [ShopSupplierProperty::PROPERTY_TYPE_LIST])) {
                            if (is_array($value)) {
                                $data = [];
                                foreach ($value as $k => $v)
                                {
                                    if ($option = $property->getShopSupplierPropertyOptions()->andWhere(['name' => $v])->one()) {
                                        if ($option->cms_tree_id) {
                                            $model->tree_id = $option->cms_tree_id;
                                        }
                                        $data[] = $option->cms_content_element_id ? $option->cms_content_element_id : $option->cms_content_property_enum_id;
                                    }
                                }
                                $model->relatedPropertiesModel->setAttribute($code, $data);
                            } else {
                                if ($option = $property->getShopSupplierPropertyOptions()->andWhere(['name' => $value])->one()) {
                                    if ($option->cms_tree_id) {
                                        $model->tree_id = $option->cms_tree_id;
                                    }
                                    $model->relatedPropertiesModel->setAttribute($code, $option->cms_content_element_id ? $option->cms_content_element_id : $option->cms_content_property_enum_id);
                                }
                            }
                        } elseif (in_array($property->property_type, [ShopSupplierProperty::PROPERTY_TYPE_STRING, ShopSupplierProperty::PROPERTY_TYPE_NUMBER])) {
                            if (is_array($value)) {

                            } else {
                                $model->relatedPropertiesModel->setAttribute($code, $value);
                            }
                        }
                    }
                }
            }
        }
    }

}