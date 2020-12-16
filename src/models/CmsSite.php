<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\models;

/**
 * @property ShopDelivery[]         $shopDeliveries
 * @property ShopFavoriteProduct[]  $shopFavoriteProducts
 * @property ShopImportCmsSite[]    $shopImportCmsSites
 * @property ShopImportCmsSite[]    $senderShopImportCmsSites
 * @property ShopOrder[]            $shopOrders
 * @property ShopPaySystem[]        $shopPaySystems
 * @property ShopPersonTypeSite[]   $shopPersonTypeSites
 * @property ShopSite               $shopSite
 * @property ShopStore[]            $shopStores
 * @property ShopTypePrice[]        $shopTypePrices
 * @property ShopSupplierProperty[] $shopSupplierProperties
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class CmsSite extends \skeeks\cms\models\CmsSite
{
    public function init()
    {
        $this->on(self::EVENT_AFTER_INSERT, [$this, "_afterInsterEvent"]);
        parent::init();
    }

    /**
     * 
     */
    public function _afterInsterEvent()
    {
        $shopSite = new ShopSite();
        $shopSite->id = $this->id;
        $shopSite->save();
    }
    
    /**
     * Gets query for [[ShopDeliveries]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopDeliveries()
    {
        return $this->hasMany(ShopDelivery::className(), ['site_id' => 'id']);
    }

    /**
     * Gets query for [[ShopFavoriteProducts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopFavoriteProducts()
    {
        return $this->hasMany(ShopFavoriteProduct::className(), ['cms_site_id' => 'id']);
    }

    /**
     * Gets query for [[ShopImportCmsSites]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopImportCmsSites()
    {
        return $this->hasMany(ShopImportCmsSite::className(), ['cms_site_id' => 'id']);
    }

    /**
     * Gets query for [[ShopImportCmsSites0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSenderShopImportCmsSites()
    {
        return $this->hasMany(ShopImportCmsSite::className(), ['sender_cms_site_id' => 'id']);
    }

    /**
     * Gets query for [[ShopOrders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopOrders()
    {
        return $this->hasMany(ShopOrder::className(), ['cms_site_id' => 'id']);
    }

    /**
     * Gets query for [[ShopPaySystems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopPaySystems()
    {
        return $this->hasMany(ShopPaySystem::className(), ['cms_site_id' => 'id']);
    }

    /**
     * Gets query for [[ShopPersonTypeSites]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopPersonTypeSites()
    {
        return $this->hasMany(ShopPersonTypeSite::className(), ['cms_site_id' => 'id']);
    }

    /**
     * Gets query for [[ShopSite]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopSite()
    {
        return $this->hasOne(ShopSite::className(), ['id' => 'id']);
    }

    /**
     * Gets query for [[ShopStores]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopStores()
    {
        return $this->hasMany(ShopStore::className(), ['cms_site_id' => 'id']);
    }

    /**
     * Gets query for [[ShopTypePrices]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopTypePrices()
    {
        return $this->hasMany(ShopTypePrice::className(), ['cms_site_id' => 'id'])->orderBy(['priority' => SORT_ASC]);
    }

    /**
     * Gets query for [[ShopTypePrices]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShopSupplierProperties()
    {
        return $this->hasMany(ShopSupplierProperty::className(), ['cms_site_id' => 'id']);
    }
}