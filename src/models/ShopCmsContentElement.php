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
 * @property ShopCmsContentElement   $mainCmsContentElement
 * @property ShopCmsContentElement[] $shopSellerElements
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

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [
                ['main_cce_id'],
                function ($attribute) {

                    /**
                     * @var $cce ShopCmsContentElement
                     * @var $shopProduct ShopProduct
                     */
                    $cce = self::find()->where(['id' => $this->main_cce_id])->one();
                    $shopProduct = $cce->shopProduct;
                    if (!in_array($shopProduct->product_type, [
                        ShopProduct::TYPE_SIMPLE,
                        ShopProduct::TYPE_OFFER,
                    ])) {
                        $this->addError("main_cce_id", "Родительский товар должен быть простым или предложением.");
                        return false;
                    }

                    if (!$cce->cmsSite->is_default) {
                        $this->addError("main_cce_id", "Родительский товар, должен относится к главному порталу!!!");
                        return false;
                    }

                    //Это товар принадлежит сайту получателю
                    if ($this->cmsSite->shopSite->is_receiver) {
                        $qExist = ShopCmsContentElement::find()
                            ->cmsSite($this->cmsSite)
                            ->joinWith('shopProduct as sp', true, "INNER JOIN")
                            ->andWhere([ShopCmsContentElement::tableName().'.main_cce_id' => $this->main_cce_id]);

                        if (!$this->isNewRecord) {
                            $qExist->andWhere(["!=", ShopCmsContentElement::tableName().'.id', $this->id]);
                        }

                        if ($exist = $qExist->one()) {
                            $this->addError("main_cce_id", "Вы пытаетесь привязать товар к инфо карточке, которая уже есть на вашем сайте. id=".$exist->id);
                            return false;
                        }
                    }
                },
            ],
        ]);
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
        if ($this->main_cce_id && $this->mainCmsContentElement) {
            if ($images = $this->mainCmsContentElement->images) {
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
                if ($parent->cmsContentElement->main_cce_id) {
                    if ($images = $parent->cmsContentElement->mainCmsContentElement->images) {
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
        if ($this->main_cce_id) {
            if ($image = $this->mainCmsContentElement->mainProductImage) {
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
                if ($parent->cmsContentElement->main_cce_id) {
                    if ($image = $parent->cmsContentElement->mainCmsContentElement->image) {
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
        if ($this->main_cce_id) {
            if ($description_short = $this->mainCmsContentElement->description_short) {
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
                if ($parent->cmsContentElement->main_cce_id) {
                    if ($description_short = $parent->cmsContentElement->mainCmsContentElement->description_short) {
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
        if ($this->main_cce_id) {
            if ($description_full = trim($this->mainCmsContentElement->description_full)) {
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
                if ($parent->cmsContentElement->main_cce_id) {
                    if ($description_full = $parent->cmsContentElement->mainCmsContentElement->description_full) {
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
            if ($this->main_cce_id) {
                return $this->mainCmsContentElement->seoName;
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
            if ($this->main_cce_id) {
                return $this->mainCmsContentElement->productName;
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


    /**
     * Товары на сайтах для продажи
     * @return \yii\db\ActiveQuery
     */
    public function getShopSellerElements()
    {
        $q = $this->getSecondaryCmsContentElements()
            ->joinWith("cmsSite as cmsSite")
            ->joinWith("cmsSite.shopSite as shopSite")
            ->andWhere(['shopSite.is_receiver' => 1]);

        return $q;
    }
}