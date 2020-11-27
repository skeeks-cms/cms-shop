<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 06.03.2016
 */

namespace skeeks\cms\shop\models;

use skeeks\cms\helpers\StringHelper;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsStorageFile;
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
 * @property CmsStorageFile          $mainProductImage
 * @property CmsStorageFile[]        $productImages
 * @property string                  $productDescriptionShort
 * @property string                  $productDescriptionFull
 * @property string                  $productName
 * @property CmsSite                 $cmsSite
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
     * @deprecated
     */
    public function getTradeOffers()
    {
        return $this->shopProduct->getTradeOffers();
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
        return "#".$this->id."#".$this->productName;

        $text = parent::asText();

        $result = [];
        //Если это предложение, то надо в заголовок добавить ключевые свойства.
        if ($this->shopProduct && $this->shopProduct->isOfferProduct) {
            if (\Yii::$app->shop->offerCmsContentProperties) {
                foreach (\Yii::$app->shop->offerCmsContentProperties as $cmsContentProperty) {
                    if ($value = $this->relatedPropertiesModel->getAttribute($cmsContentProperty->code)) {
                        $result[] = $this->relatedPropertiesModel->getAttributeAsText($cmsContentProperty->code);
                    }
                }
            }
        }

        if ($result) {
            $text .= " [".implode(", ", $result)."]";
        }

        return $text;
    }


    public function loadDataToMainModel(ShopCmsContentElement $model)
    {
        $model->name = $this->name;

        if ($this->shopProduct && $this->shopProduct->supplier_external_jsondata) {


            foreach ($this->shopProduct->supplier_external_jsondata as $key => $value) {
                /**
                 * @var $property ShopSupplierProperty
                 * @var $option ShopSupplierPropertyOption
                 */
                if ($property = $this->shopProduct->cmsContentElement->cmsSite
                    ->getShopSupplierProperties()->andWhere(['external_code' => $key])
                    ->one()) {
                    if ($property->cmsContentProperty) {
                        $code = $property->cmsContentProperty->code;
                        if (in_array($property->property_type, [ShopSupplierProperty::PROPERTY_TYPE_LIST])) {
                            if ($property->import_delimetr) {
                                $value = explode($property->import_delimetr, $value);
                                foreach ($value as $k => $v) {
                                    $value[$k] = trim($v);
                                }
                            }

                            if (is_array($value)) {
                                $data = [];
                                foreach ($value as $k => $v) {
                                    if ($option = $property->getShopSupplierPropertyOptions()->andWhere(['name' => $v])->one()) {
                                        if ($option->cms_tree_id) {
                                            $model->tree_id = $option->cms_tree_id;
                                        }
                                        $data[] = $option->cms_content_element_id ? $option->cms_content_element_id : $option->cms_content_property_enum_id;
                                    }
                                }
                                //$model->relatedPropertiesModel->setAttribute($code, $data);
                            } else {
                                if ($option = $property->getShopSupplierPropertyOptions()->andWhere(['name' => $value])->one()) {
                                    if ($option->cms_tree_id) {
                                        $model->tree_id = $option->cms_tree_id;
                                    }
                                    //$model->relatedPropertiesModel->setAttribute($code, $option->cms_content_element_id ? $option->cms_content_element_id : $option->cms_content_property_enum_id);
                                }
                            }
                        } elseif (in_array($property->property_type, [ShopSupplierProperty::PROPERTY_TYPE_STRING, ShopSupplierProperty::PROPERTY_TYPE_NUMBER])) {
                            if (is_array($value)) {

                            } else {
                                //$model->relatedPropertiesModel->setAttribute($code, $value);
                            }
                        }
                    }
                }
            }


            //print_r($model->relatedPropertiesModel->toArray());
            /*
                        print_r(count($model->relatedProperties));
                        print_r($model->relatedPropertiesModel->initAllProperties());
                        print_r($model->relatedPropertiesModel->toArray());
                        print_r($model->toArray());*/
            ///die;

            foreach ($this->shopProduct->supplier_external_jsondata as $key => $value) {
                /**
                 * @var $property ShopSupplierProperty
                 * @var $option ShopSupplierPropertyOption
                 */
                if ($property = $this->shopProduct->cmsContentElement->cmsSite
                    ->getShopSupplierProperties()->andWhere(['external_code' => $key])
                    ->one()) {
                    if ($property->cmsContentProperty) {
                        $code = $property->cmsContentProperty->code;
                        if (in_array($property->property_type, [ShopSupplierProperty::PROPERTY_TYPE_LIST])) {
                            if ($property->import_delimetr) {
                                $value = explode($property->import_delimetr, $value);
                                foreach ($value as $k => $v) {
                                    $value[$k] = trim($v);
                                }
                            }

                            if (is_array($value)) {
                                $data = [];
                                foreach ($value as $k => $v) {
                                    if ($option = $property->getShopSupplierPropertyOptions()->andWhere(['name' => $v])->one()) {
                                        if ($option->cms_tree_id) {
                                            $model->tree_id = $option->cms_tree_id;
                                        }
                                        $data[] = $option->cms_content_element_id ? $option->cms_content_element_id : $option->cms_content_property_enum_id;
                                    }
                                }
                                if ($model->relatedPropertiesModel->hasAttribute($code)) {
                                    $model->relatedPropertiesModel->setAttribute($code, $data);
                                }

                            } else {
                                if ($option = $property->getShopSupplierPropertyOptions()->andWhere(['name' => $value])->one()) {
                                    if ($option->cms_tree_id) {
                                        $model->tree_id = $option->cms_tree_id;
                                    }

                                    if ($model->relatedPropertiesModel->hasAttribute($code)) {
                                        $model->relatedPropertiesModel->setAttribute($code, $option->cms_content_element_id ? $option->cms_content_element_id : $option->cms_content_property_enum_id);
                                    }


                                }
                            }
                        } elseif (in_array($property->property_type, [ShopSupplierProperty::PROPERTY_TYPE_STRING, ShopSupplierProperty::PROPERTY_TYPE_NUMBER])) {
                            if (is_array($value)) {

                            } else {
                                if ($model->relatedPropertiesModel->hasAttribute($code)) {
                                    $model->relatedPropertiesModel->setAttribute($code, $value);
                                }
                            }
                        }
                    }
                }
            }
        }
    }


    /**
     * Вовзращает дополнительные картинки по товару
     * Включая всю логику главной привязки товара, товара предложения и прочего.
     *
     * @return array|CmsStorageFile[]|null
     */
    public function getProductImages()
    {
        if ($this->images) {
            return $this->images;
        }

        if (!$shopProduct = $this->shopProduct) {
            return null;
        }

        //Если у товара задан главный товар и у него есть картинка, берем ее
        if ($shopProduct->main_pid && $shopProduct->shopMainProduct) {
            if ($images = $shopProduct->shopMainProduct->cmsContentElement->images) {
                return $images;
            }
        }


        //Если это товар предложение и у общего товара задана картинка то покажем ее
        if ($shopProduct->isOfferProduct) {
            if ($parent = $this->shopProduct->shopProductWhithOffers) {
                if ($images = $parent->cmsContentElement->images) {
                    return $images;
                }

                //Если общий товар связан с главным и у него есть картинка берем ее
                if ($parent->main_pid && $parent->shopMainProduct) {
                    if ($images = $parent->shopMainProduct->cmsContentElement->images) {
                        return $images;
                    }
                }

            }
        }

        return [];
    }

    /**
     * Возвращает главное изображение товара
     * Включая всю логику главной привязки товара, товара предложения и прочего.
     *
     * @return \skeeks\cms\models\CmsStorageFile|null
     */
    public function getMainProductImage()
    {
        //Главная картинка есть, больше ничего проверять не нужно
        if ($this->image) {
            return $this->image;
        }

        //У элемента нет товара, так же ничего проверять не нужно
        if (!$shopProduct = $this->shopProduct) {
            return null;
        }

        //Если у товара задан главный товар и у него есть картинка, берем ее
        if ($shopProduct->main_pid && $shopProduct->shopMainProduct) {
            if ($image = $shopProduct->shopMainProduct->cmsContentElement->image) {
                return $image;
            }
        }

        //Если это товар предложение и у общего товара задана картинка то покажем ее
        if ($shopProduct->isOfferProduct) {
            if ($parent = $this->shopProduct->shopProductWhithOffers) {
                if ($image = $parent->cmsContentElement->image) {
                    return $image;
                }

                //Если общий товар связан с главным и у него есть картинка берем ее
                if ($parent->main_pid && $parent->shopMainProduct) {
                    if ($image = $parent->shopMainProduct->cmsContentElement->image) {
                        return $image;
                    }
                }

            }
        }


        return null;
    }


    /**
     * @return string
     */
    public function getProductDescriptionShort()
    {
        if ($this->description_short) {
            return $this->description_short;
        }

        //У элемента нет товара, так же ничего проверять не нужно
        if (!$shopProduct = $this->shopProduct) {
            return "";
        }

        //Если у товара задан главный товар и у него есть картинка, берем ее
        if ($shopProduct->main_pid && $shopProduct->shopMainProduct) {
            if ($description_short = $shopProduct->shopMainProduct->cmsContentElement->description_short) {
                return $description_short;
            }
        }

        //Если это товар предложение и у общего товара задана картинка то покажем ее
        if ($shopProduct->isOfferProduct) {
            if ($parent = $this->shopProduct->shopProductWhithOffers) {
                if ($description_short = $parent->cmsContentElement->description_short) {
                    return $description_short;
                }

                //Если общий товар связан с главным и у него есть картинка берем ее
                if ($parent->main_pid && $parent->shopMainProduct) {
                    if ($description_short = $parent->shopMainProduct->cmsContentElement->description_short) {
                        return $description_short;
                    }
                }

            }
        }

        return "";
    }

    /**
     * @return string
     */
    public function getProductDescriptionFull()
    {
        if ($this->description_full) {
            return $this->description_full;
        }

        //У элемента нет товара, так же ничего проверять не нужно
        if (!$shopProduct = $this->shopProduct) {
            return "";
        }

        //Если у товара задан главный товар и у него есть картинка, берем ее
        if ($shopProduct->main_pid && $shopProduct->shopMainProduct) {
            if ($description_full = trim($shopProduct->shopMainProduct->cmsContentElement->description_full)) {
                return $description_full;
            }
        }

        //Если это товар предложение и у общего товара задана картинка то покажем ее
        if ($shopProduct->isOfferProduct) {

            if ($parent = $shopProduct->shopProductWhithOffers) {
                if ($description_full = $parent->cmsContentElement->description_full) {
                    return $description_full;
                }

                //Если общий товар связан с главным и у него есть картинка берем ее
                if ($parent->main_pid && $parent->shopMainProduct) {
                    if ($description_full = $parent->shopMainProduct->cmsContentElement->description_full) {
                        return $description_full;
                    }
                }
            }
        }

        return "";
    }

    public function getSeoName()
    {
        $name = parent::getSeoName();
        //Если это оффер
        if ($this->shopProduct && $this->shopProduct->isOfferProduct) {
            $result = [];
            if ($this->shopProduct->main_pid) {
                return $this->shopProduct->shopMainProduct->cmsContentElement->seoName;
            }

            $name = $this->shopProduct->shopProductWhithOffers->cmsContentElement->seoName;
            if (\Yii::$app->shop->offerCmsContentProperties) {
                foreach (\Yii::$app->shop->offerCmsContentProperties as $cmsContentProperty) {
                    if ($value = $this->relatedPropertiesModel->getAttribute($cmsContentProperty->code)) {
                        if ($measure = $cmsContentProperty->cmsMeasure) {
                            $result[] = StringHelper::strtolower($this->relatedPropertiesModel->getAttributeAsText($cmsContentProperty->code).$measure->symbol);
                        } else {

                            $result[] = StringHelper::strtolower($this->relatedPropertiesModel->getAttributeAsText($cmsContentProperty->code));
                        }

                    }
                }
            }

            if ($result) {
                $name = trim($name).", ".implode(", ", $result);
            }
        }

        return $name;
    }


    /**
     * @return string
     */
    public function getProductName()
    {
        $name = $this->name;
        //Если это оффер
        if ($this->shopProduct && $this->shopProduct->isOfferProduct) {
            $result = [];
            if ($this->shopProduct->main_pid) {
                return $this->shopProduct->shopMainProduct->cmsContentElement->productName;
            }

            $name = $this->shopProduct->shopProductWhithOffers->cmsContentElement->name;
            if (\Yii::$app->shop->offerCmsContentProperties) {
                foreach (\Yii::$app->shop->offerCmsContentProperties as $cmsContentProperty) {
                    if ($value = $this->relatedPropertiesModel->getAttribute($cmsContentProperty->code)) {
                        if ($measure = $cmsContentProperty->cmsMeasure) {
                            $result[] = StringHelper::strtolower($this->relatedPropertiesModel->getAttributeAsText($cmsContentProperty->code).$measure->symbol);
                        } else {

                            $result[] = StringHelper::strtolower($this->relatedPropertiesModel->getAttributeAsText($cmsContentProperty->code));
                        }

                    }
                }
            }

            if ($result) {
                $name = trim($name).", ".implode(", ", $result);
            }
        }

        return $name;
    }
}