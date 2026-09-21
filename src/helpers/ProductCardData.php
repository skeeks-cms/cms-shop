<?php
namespace skeeks\cms\shop\helpers;

use skeeks\cms\shop\models\ShopStoreProduct;
use yii\helpers\ArrayHelper;

/**
 * Snapshot for one rendered list, using the current shop/user/store context.
 * Never store this object in a shared cache or reuse it after a cart mutation.
 */
class ProductCardData
{
    private $products = [];
    private $storeProducts = [];
    private $favorites = [];
    private $comparisons = [];

    /**
     * @param \skeeks\cms\shop\models\ShopCmsContentElement[] $models Current page only.
     * @param \skeeks\cms\shop\components\ShopComponent $shop
     * @return static
     */
    public static function load(array $models, $shop)
    {
        $data = new static();
        foreach ($models as $model) {
            if ($model->shopProduct) {
                $data->products[$model->shopProduct->id] = true;
            }
        }
        if (!$data->products) {
            return $data;
        }

        $ids = array_keys($data->products);
        $stores = $shop->allStores;
        $query = ShopStoreProduct::find()->andWhere(['shop_product_id' => $ids]);
        // Match ShopProduct::getShopStoreProducts(): an empty store list adds no filter.
        if ($stores) {
            $query->andWhere(['shop_store_id' => ArrayHelper::map($stores, 'id', 'id')]);
        }
        foreach ($query->all() as $storeProduct) {
            $data->storeProducts[$storeProduct->shop_product_id][] = $storeProduct;
        }

        // Keep the original owner relations; a guest never reads another user's flags.
        $data->favorites = array_fill_keys($shop->cart->getShopFavoriteProducts()
            ->andWhere(['shop_product_id' => $ids])->select('shop_product_id')->column(), true);
        $data->comparisons = array_fill_keys($shop->shopUser->getCmsCompareElements()
            ->andWhere(['cms_content_element_id' => $ids])->select('cms_content_element_id')->column(), true);
        return $data;
    }

    public function hasProduct($id)
    {
        return isset($this->products[$id]);
    }

    public function getStoreProducts($id)
    {
        return $this->storeProducts[$id] ?? [];
    }

    public function isFavorite($id)
    {
        return isset($this->favorites[$id]);
    }

    public function isCompared($id)
    {
        return isset($this->comparisons[$id]);
    }
}
