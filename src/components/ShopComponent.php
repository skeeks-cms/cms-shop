<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\cms\shop\components;

use skeeks\cms\backend\BackendComponent;
use skeeks\cms\backend\widgets\ActiveFormBackend;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsContentProperty;
use skeeks\cms\models\CmsSavedFilter;
use skeeks\cms\models\CmsTree;
use skeeks\cms\models\CmsUser;
use skeeks\cms\shop\models\CmsSite;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopPersonType;
use skeeks\cms\shop\models\ShopProduct;
use skeeks\cms\shop\models\ShopStore;
use skeeks\cms\shop\models\ShopTypePrice;
use skeeks\cms\shop\models\ShopUser;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\base\Event;
use yii\base\Exception;
use yii\caching\TagDependency;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\Application;
use yii\widgets\ActiveForm;

/**
 * @author Semenov Alexander <semenov@skeeks.com>
 *
 * @property ShopTypePrice        $baseTypePrice
 * @property ShopTypePrice        $purchaseTypePrice
 * @property ShopPersonType[]     $shopPersonTypes
 * @property ShopTypePrice[]      $shopTypePrices
 * @property ShopTypePrice[]      $canBuyTypePrices
 * @property ShopTypePrice[]      $canViewTypePrices
 * @property CmsContentProperty[] $offerCmsContentProperties
 *
 * @property ShopUser             $shopUser
 *
 * @depricated CmsContent           $shopContents
 * @property ShopStore[]          $stores
 * @property ShopStore[]          $supplierStores
 * @property ShopStore[]          $allStores
 * @depricated ShopContent          $cmsContent
 *
 * @property CmsContent           $contentProducts
 *
 * @property ShopStore            $backendShopStore
 */
class ShopComponent extends Component implements BootstrapInterface
{
    const SESSION_SHOP_USER_NAME = 'SKEEKS_CMS_SHOP_USER';

    /**
     * @var array
     */
    public $cloudkassaHandlers = [];

    /**
     * @var array
     */
    public $deliveryHandlers = [];

    /**
     * @var array
     */
    public $paysystemHandlers = [];

    /**
     * Максимальное допустимое количество товаров
     * @var float
     */
    public $maxQuantity = 999999;
    /**
     * Минимально допустимое количество товаров
     * @var float
     */
    public $minQuantity = 0.01;
    /**
     * @var ShopTypePrice
     */
    protected $_baseTypePrice;
    /**
     * @var ShopTypePrice
     */
    protected $_purchaseTypePrice;
    /**
     * @var array
     */
    protected $_shopTypePrices = [];
    /**
     * @var ShopUser
     */
    protected $_shopUser = null;


    public function bootstrap($application)
    {
        if ($application instanceof Application) {
            Event::on(BackendComponent::class, "beforeRun", function (Event $e) {
                $backendComponent = $e->sender;
                //Если это сайт поставщика, у него будет свое меню
            });
        }
    }

    /**
     * Можно задать название и описание компонента
     * @return array
     */
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name' => \Yii::t('skeeks/shop/app', 'Shop'),
        ]);
    }


    public function getSessionFuserName()
    {
        return static::SESSION_SHOP_USER_NAME."_".\Yii::$app->skeeks->site->id;
    }

    /**
     * @return ActiveForm
     */
    public function beginConfigForm()
    {
        return ActiveFormBackend::begin();
    }

    /**
     *
     * Тип цены по умолчанию
     *
     * @return ShopTypePrice
     */
    public function getBaseTypePrice()
    {
        if (!$this->_baseTypePrice) {
            $this->_baseTypePrice = \Yii::$app->skeeks->site->getShopTypePrices()->andWhere(['is_default' => 1])->one();
        }

        return $this->_baseTypePrice;
    }

    /**
     * Тип закупочной цены
     * @return ShopTypePrice
     */
    public function getPurchaseTypePrice()
    {
        if (!$this->_purchaseTypePrice) {
            $this->_purchaseTypePrice = \Yii::$app->skeeks->site->getShopTypePrices()->isPurchase()->one();
        }

        return $this->_purchaseTypePrice;
    }


    /**
     * @return ShopPersonType[]
     */
    public function getShopPersonTypes()
    {
        return ShopPersonType::find()->active()->all();
    }
    /**
     * Все типы цен магазина
     * @return ShopTypePrice[]
     */
    public function getShopTypePrices()
    {
        if (!$this->_shopTypePrices) {
            $this->_shopTypePrices = \Yii::$app->skeeks->site->getShopTypePrices()->orderBy(["priority" => SORT_ASC])->all();
        }

        return $this->_shopTypePrices;
    }

    /**
     * Типы цен по которым можно купить товар на сайте пользователю
     *
     * @param null|CmsUser $user
     * @return array
     */
    public function getCanBuyTypePrices($user = null)
    {
        $result = [];

        if (!$user) {
            $user = isset(\Yii::$app->user) ? \Yii::$app->user->identity : null;
        }

        foreach ($this->shopTypePrices as $typePrice) {

            if ($typePrice->isDefault) {
                $result[$typePrice->id] = $typePrice;
                continue;
            }
            if ($typePrice->is_purchase) {
                //$result[$typePrice->id] = $typePrice;
                continue;
            }

            if (!$typePrice->cmsUserRoles) {
                $result[$typePrice->id] = $typePrice;
                continue;
            }

            if ($user) {
                foreach ($typePrice->cmsUserRoles as $role) {
                    if (\Yii::$app->authManager->checkAccess($user ? $user->id : null, $role->name)) {
                        $result[$typePrice->id] = $typePrice;
                        continue;
                    }
                }
            }

        }

        return $result;
    }

    protected $_canViewTypePrice = [];
    /**
     * Типы цен которые видит клиент
     *
     * @param null|CmsUser $user
     * @return array
     */
    public function getCanViewTypePrices($user = null)
    {
        $result = [];

        if (!$user) {
            $user = \Yii::$app->user->identity;
        }

        if (isset($this->_canViewTypePrice[$user ? $user->id : "no"])) {
            return $this->_canViewTypePrice[$user ? $user->id : "no"];
        }

        foreach ($this->shopTypePrices as $typePrice) {

            if ($typePrice->isDefault) {
                $result[$typePrice->id] = $typePrice;
                continue;
            }

            if ($typePrice->is_purchase) {
                //$result[$typePrice->id] = $typePrice;
                continue;
            }

            if (!$typePrice->viewCmsUserRoles) {
                //$result[$typePrice->id] = $typePrice;
                continue;
            }

            foreach ($typePrice->viewCmsUserRoles as $role) {
                if (\Yii::$app->authManager->checkAccess($user ? $user->id : null, $role->name)) {
                    $result[$typePrice->id] = $typePrice;
                    continue;
                }
            }
        }

        $this->_canViewTypePrice[$user ? $user->id : "no"] = $result;

        return $result;
    }


    /**
     * @return array|ShopUser|\yii\db\ActiveRecord|null
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function getShopUser()
    {
        if (\Yii::$app instanceof \yii\console\Application) {
            return null;
        }

        if ($this->_shopUser instanceof ShopUser) {
            return $this->_shopUser;
        }

        $session = \Yii::$app->session;
        //Если пользователь гость
        if (isset(\Yii::$app->user) && \Yii::$app->user && \Yii::$app->user->isGuest) {
            //Проверка сессии
            if ($session->getHasSessionId() || $session->getIsActive()) {
                if ($session->offsetExists($this->sessionFuserName)) {
                    $fuserId = $session->get($this->sessionFuserName);
                    $shopCart = ShopUser::find()->where(['id' => $fuserId])->one();
                    //Поиск юзера
                    if ($shopCart) {
                        $this->_shopUser = $shopCart;
                    }
                }
            }

            if (!$this->_shopUser) {
                $shopCart = new ShopUser();
                $this->_shopUser = $shopCart;
            }
        } else {
            //Если пользователь авторизован
            $this->_shopUser = ShopUser::find()
                ->where(['cms_user_id' => \Yii::$app->user->identity->id])
                ->andWhere(['cms_site_id' => \Yii::$app->skeeks->site->id])
                ->one();

            //Если у авторизовнного пользоывателя уже есть пользователь корзины
            if ($this->_shopUser) {
                //Проверка сессии, а было ли чего то в корзине
                if ($session->offsetExists($this->sessionFuserName)) {
                    $fuserId = $session->get($this->sessionFuserName);
                    $shopCart = ShopUser::find()->where(['id' => $fuserId])->one();

                    /**
                     * @var $shopCart ShopUser
                     */
                    if ($shopCart) {
                        $this->_shopUser->shopOrder->addShopOrderItems($shopCart->shopOrder->shopOrderItems);
                        $shopCart->delete();
                    }

                    //Эти данные в сессии больше не нужны
                    $session->remove($this->sessionFuserName);
                }
            } else {
                //Проверка сессии, а было ли чего то в корзине
                if ($session->offsetExists($this->sessionFuserName)) {
                    $fuserId = $session->get($this->sessionFuserName);
                    $shopCart = ShopUser::find()->where(['id' => $fuserId])->one();
                    //Поиск юзера
                    /**
                     * @var $shopCart ShopUser
                     */
                    if ($shopCart) {
                        $shopCart->cms_user_id = \Yii::$app->user->identity->id;
                        $shopCart->save();
                    }

                    $this->_shopUser = $shopCart;
                    $session->remove($this->sessionFuserName);
                } else {
                    $shopCart = new ShopUser([
                        'cms_user_id' => \Yii::$app->user->identity->id,
                    ]);

                    if (!$shopCart->save()) {
                        throw new Exception(print_r($shopCart->errors, true));
                    }

                    $this->_shopUser = $shopCart;
                }
            }
        }

        return $this->_shopUser;
    }

    /**
     * @param ShopUser $shopCart
     * @return $this
     */
    public function setCart(ShopUser $shopCart)
    {
        $this->_shopUser = $shopCart;
        return $this;
    }

    /**
     * @return $this
     * @deprecated
     */
    public function getShopContents()
    {
        $result = [];
        if ($this->contentProducts) {
            $result[] = $this->contentProducts;
        }

        return $result;
    }

    /**
     * @return $this
     * @deprecated
     */
    public function getCmsContent()
    {
        return $this->getContentProducts()->one();
    }


    /**
     * @return CmsContent|null
     */
    public function getContentProducts()
    {
        return CmsContent::find()->isProducts()->one();
    }


    /**
     * TODO: is @return array
     * @deprecated remove it!
     */
    public function getArrayForSelectElement()
    {

        if (!$data = CmsContent::getDataForSelect()) {
            return [];
        }

        $ids = [\Yii::$app->shop->contentProducts->id];

        $result = [];
        foreach ($data as $typeKey => $type) {
            if ($type) {
                $contents = [];
                foreach ($type as $key => $value) {
                    if (in_array($key, $ids)) {
                        $contents[$key] = $value;
                    }
                }

                if ($contents) {
                    $result[$typeKey] = $contents;
                }
            }
        }

        return $result;
    }

    /**
     *
     * Фильтрация базового запроса на выборку товаров с учетом настроек магазина.
     *
     * @param ActiveQuery $activeQuery
     * @return $this
     */
    public function filterBaseContentElementQuery(ActiveQuery $activeQuery)
    {
        $this
            ->filterByTypeContentElementQuery($activeQuery)
            ->filterByPriceContentElementQuery($activeQuery)//->filterByMainPidContentElementQuery($activeQuery)
        ;

        return $this;
    }


    /**
     * @param ActiveQuery $activeQuery
     * @return $this
     */
    public function filterByQuantityQuery(ActiveQuery $activeQuery)
    {
        if (\Yii::$app->skeeks->site->shopSite->is_show_product_only_quantity == 1) {

            $storeIds = [];
            if ($this->stores) {
                $storeIds = ArrayHelper::map($this->stores, "id", "id");
            }

            $activeQuery->joinWith('shopProduct as shopProduct');

            //$activeQuery->joinWith('shopProduct.shopStoreProducts as shopStoreProducts', );
            //$activeQuery->joinWith('shopProduct.shopProductOffers.shopStoreProducts as shopOffersStoreProducts');

            $activeQuery->leftJoin(["shopStoreProducts" => "shop_store_product"], [
                "shopStoreProducts.shop_product_id" => new Expression("shopProduct.id"),
                "shopStoreProducts.shop_store_id"   => $storeIds,
            ]);

            $activeQuery->joinWith('shopProduct.shopProductOffers as shopProductOffers');
            $activeQuery->leftJoin(["shopOffersStoreProducts" => "shop_store_product"], [
                "shopOffersStoreProducts.shop_product_id" => new Expression("shopProductOffers.id"),
                "shopOffersStoreProducts.shop_store_id"   => $storeIds,
            ]);

            $activeQuery->andWhere([
                'or',
                ['>', 'shopStoreProducts.quantity', 0],
                ['>', 'shopOffersStoreProducts.quantity', 0],
            ]);
            $activeQuery->groupBy([ShopCmsContentElement::tableName().".id"]);


        } elseif (\Yii::$app->skeeks->site->shopSite->is_show_product_only_quantity == 2) {

            $storeIds = [];
            if ($this->stores) {
                $storeIds = ArrayHelper::map($this->stores, "id", "id");
            }
            if ($this->supplierStores) {
                $supploerStoreIds = ArrayHelper::map($this->supplierStores, "id", "id");
                $storeIds = ArrayHelper::merge($storeIds, $supploerStoreIds);
            }


            $activeQuery->joinWith('shopProduct as shopProduct');

            $activeQuery->leftJoin(["shopStoreProducts" => "shop_store_product"], [
                "shopStoreProducts.shop_product_id" => new Expression("shopProduct.id"),
                "shopStoreProducts.shop_store_id"   => $storeIds,
            ]);

            $activeQuery->joinWith('shopProduct.shopProductOffers as shopProductOffers');
            $activeQuery->leftJoin(["shopOffersStoreProducts" => "shop_store_product"], [
                "shopOffersStoreProducts.shop_product_id" => new Expression("shopProductOffers.id"),
                "shopOffersStoreProducts.shop_store_id"   => $storeIds,
            ]);

            /*$activeQuery->joinWith('shopProduct.shopProductOffers as shopProductOffers');

            $activeQuery->joinWith('shopProduct.shopStoreProducts as shopStoreProducts');
            $activeQuery->joinWith('shopProduct.shopProductOffers.shopStoreProducts as shopOffersStoreProducts');*/

            $activeQuery->andWhere([
                'or',
                ['>', 'shopStoreProducts.quantity', 0],
                ['>', 'shopOffersStoreProducts.quantity', 0],
            ]);
            $activeQuery->groupBy([ShopCmsContentElement::tableName().".id"]);
        }

        return $this;
    }

    /**
     * @param ActiveQuery $activeQuery
     * @return $this
     */
    public function filterByTypeContentElementQuery(ActiveQuery $activeQuery)
    {
        $activeQuery->joinWith("shopProduct as shopProduct");
        $activeQuery->andWhere([
            '!=',
            'shopProduct.product_type',
            \skeeks\cms\shop\models\ShopProduct::TYPE_OFFER,
        ]);
        return $this;
    }


    /**
     *
     * Фильтрация базового запроса на выборку товаров с учетом настроек магазина.
     *
     * @param ActiveQuery $activeQuery
     * @return $this
     */
    public function filterByPriceContentElementQuery(ActiveQuery $activeQuery)
    {
        if (!\Yii::$app->skeeks->site->shopSite->is_show_product_no_price) {
            $activeQuery->joinWith('shopProduct.shopProductPrices as pricesFilter');
            $activeQuery->andWhere(['>', '`pricesFilter`.price', 0]);
        }

        return $this;
    }


    /**
     * Товарные данные обновляются из главных товаров
     * Габариты, вес, соответствие величин
     *
     * @return $this
     * @throws \yii\db\Exception
     */
    public function updateAllSubproducts()
    {
        $result = \Yii::$app->db->createCommand(<<<SQL
            UPDATE 
                `shop_product` as sp 
                INNER JOIN (
                    /*Товары у которых задан главный товар*/
                    SELECT 
                        inner_cce.id as inner_sp_id,
                        inner_cce.main_cce_id as main_cce_id
                    FROM 
                        cms_content_element inner_cce 
                    WHERE 
                        inner_cce.main_cce_id is not null
                ) sp_has_main_pid ON sp_has_main_pid.inner_sp_id = sp.id 
                LEFT JOIN shop_product as sp_main on sp_main.id = sp_has_main_pid.main_cce_id 
            SET 
                sp.`measure_ratio` = sp_main.measure_ratio, 
                sp.`measure_ratio_min` = sp_main.measure_ratio_min, 
                sp.`measure_matches_jsondata` = sp_main.measure_matches_jsondata, 
                sp.`measure_code` = sp_main.measure_code, 
                sp.`width` = sp_main.width, 
                sp.`length` = sp_main.length, 
                sp.`height` = sp_main.height, 
                sp.`weight` = sp_main.weight
SQL
        )->execute();

        return $this;
    }


    /**
     *
     * Товары у которых не задан родительский элемент делает простыми
     * Товары у которых есть дочерние делает товарами с предложенями
     * Обновляет раздел для товаров предложений. Раздел должнен совпадать с родительским, общим товаром
     * Товары у которых задан общий делает товарами-предложениями
     *
     * @return $this
     * @throws \yii\db\Exception
     */
    public function updateAllTypes()
    {

        //Удаляет товары с сайтов получателей, которые не связаны с главным
        if (!\Yii::$app->shop->contentProducts) {
            return false;
        }

        $content_ids = [\Yii::$app->shop->contentProducts->id];
        $content_ids_row = implode(",", $content_ids);

        /*$result = \Yii::$app->db->createCommand(<<<SQL
    DELETE cce_for_delete 
    FROM 
        cms_content_element as cce_for_delete 
        INNER JOIN (
            SELECT 
                sp.id 
            FROM 
                `shop_product` as sp 
                LEFT JOIN cms_content_element as cce on sp.id = cce.id 
                LEFT JOIN shop_site shop_site on shop_site.id = cce.cms_site_id 
            WHERE 
                shop_site.is_receiver = 1 /*Касается только сайтов получаетелей
                AND cce.`main_cce_id` is null
                AND cce.`content_id` in ({$content_ids_row})
            /*LIMIT 1
        ) as not_hav_main_pid ON not_hav_main_pid.id = cce_for_delete.id
SQL
        );*/

        //Товары у которых не задан родительский элемент и нет вложенных делаем простыми
        $result = \Yii::$app->db->createCommand(<<<SQL
            UPDATE 
                `shop_product` as sp 
                INNER JOIN (
                    SELECT 
                        inner_sp.id 
                    FROM 
                        `shop_product` as inner_sp 
                        LEFT JOIN `shop_product` as sp_offers ON sp_offers.offers_pid = inner_sp.id 
                    WHERE 
                        inner_sp.offers_pid is null /*не задан общий товар*/
                        and sp_offers.id is null /*к товару никто не привязан*/
                        and inner_sp.product_type != 'simple' /*Не простой товар*/
                        GROUP BY inner_sp.id
                ) as join_sp on join_sp.id = sp.id
            SET 
                sp.`product_type` = "simple"
SQL
        )->execute();

        //Товары у которых есть дочерние - товарами с предложенями
        $result = \Yii::$app->db->createCommand(<<<SQL
            UPDATE 
                `shop_product` as sp 
                INNER JOIN
                (
                    SELECT 
                        inner_sp.* 
                    FROM 
                        `shop_product` as inner_sp 
                        LEFT JOIN `shop_product` as sp_offers ON sp_offers.offers_pid = inner_sp.id 
                    WHERE 
                        sp_offers.id is not null /*к товару кто то привязан*/
                        AND inner_sp.product_type != 'offers' /*И у которого неправильный тип*/
                    GROUP BY 
                        inner_sp.id 
                ) sp_has_parent ON sp.id = sp_has_parent.id
            SET 
                sp.`product_type` = "offers"
SQL
        )->execute();

        //Товар-предложение
        $result = \Yii::$app->db->createCommand(<<<SQL
            UPDATE 
                `shop_product` as sp 
                INNER JOIN
                (
                    /*Товары которые являются предложениями */
                   SELECT inner_sp.id as inner_sp_id
                   FROM shop_product inner_sp
                   WHERE inner_sp.offers_pid is not null
                   AND inner_sp.product_type != 'offer'
                   GROUP BY inner_sp.id
                ) sp_has_parent ON sp.id = sp_has_parent.inner_sp_id
            SET 
                sp.`product_type` = "offer"
SQL
        )->execute();


        //У товаров предложений раздел должнен совпадать с родительским
        $result = \Yii::$app->db->createCommand(<<<SQL
            UPDATE 
                `cms_content_element` as cce 
                INNER JOIN
                (
                    /*Товары которые являются предложениями */
                   SELECT 
                       inner_sp.id as inner_sp_id,
                        inner_sp.offers_pid,
                        inner_sp.product_type
                   FROM shop_product inner_sp
                   WHERE inner_sp.offers_pid is not null
                   GROUP BY inner_sp.id
                ) sp_has_parent ON cce.id = sp_has_parent.inner_sp_id
                LEFT JOIN shop_product as offers_sp on offers_sp.id = sp_has_parent.offers_pid
                LEFT JOIN cms_content_element as offers_cce on offers_cce.id = offers_sp.id
            SET 
                cce.`tree_id` = offers_cce.tree_id
SQL
        )->execute();

        return $this;

        //У товаров на сайта приемщиках должны быть заданы правильно разделы и названия
        $result = \Yii::$app->db->createCommand(<<<SQL
UPDATE 
	`cms_content_element` as update_cce 
	INNER JOIN (
		SELECT 
			ce.id, 
			ce.cms_site_id, 
			ce_main.name as model_name, 
			ce.name, 
			ce.tree_id, 
			new_cms_tree.id as new_tree_id 
		FROM 
			
			/* Товары */
			cms_content_element as ce 
			LEFT JOIN shop_product as sp ON sp.id = ce.id 
			/* Сайты */
			LEFT JOIN shop_site as shopSite ON shopSite.id = ce.cms_site_id 
			/* Модели */
			LEFT JOIN shop_product as sp_main ON sp_main.id = ce.main_cce_id 
			LEFT JOIN cms_content_element as ce_main ON sp_main.id = ce_main.id 
			LEFT JOIN cms_tree as source_tree ON source_tree.id = ce_main.tree_id 
			/* Разделы товаров на новом сайте */
			LEFT JOIN cms_tree as new_cms_tree ON new_cms_tree.main_cms_tree_id = ce_main.tree_id 
		WHERE 
			shopSite.is_receiver = 1 
			AND ce.main_cce_id is not NULL 
			AND new_cms_tree.cms_site_id = ce.cms_site_id 
			AND (
				ce.tree_id is NULL 
				OR ce.tree_id != new_cms_tree.id 
				OR ce_main.name != ce.name
			)
	) as ready_cce ON update_cce.id = ready_cce.id 
SET 
	update_cce.`name` = ready_cce.model_name, 
	update_cce.tree_id = ready_cce.new_tree_id

SQL
        )->execute();

        return $this;
    }

    /**
     * Обновление цен у общих товаров с пердложениями
     *
     * @return $this
     * @throws \yii\db\Exception
     */
    public function updateOffersPrice()
    {
        /**
         * Вставка недостающих цен для общих товаров и их обновление
         */
        \Yii::$app->db->createCommand(<<<SQL
/*Создание недостающих цен у товаров с пердложениями*/
INSERT IGNORE
    INTO shop_product_price (`product_id`, `type_price_id`, `price`, `currency_code`)
select
	/*sp.offers_pid,
    sp_price.type_price_id,
    min(sp_price.price) as min_price,
    sp_price.**/

    sp.offers_pid,
    sp_price.type_price_id,
    min(sp_price.price) as min_price,
    sp_price.currency_code
from
	shop_product_price as sp_price
	LEFT JOIN shop_product as sp on sp.id = sp_price.product_id
	LEFT JOIN shop_product as sp_with_offers on sp_with_offers.id = sp.offers_pid
WHERE
	sp.offers_pid is not null
GROUP BY
	sp.offers_pid,
	sp_price.type_price_id
ORDER BY `sp`.`offers_pid`  DESC
SQL
        )->execute();

        \Yii::$app->db->createCommand(<<<SQL
/*Обновление цены от у товаров с предложениями*/
UPDATE
	`shop_product_price` as price
	INNER JOIN (
		select

			/*sp.offers_pid,
						    sp_price.type_price_id,
						    min(sp_price.price) as min_price,
						    sp_price.**/
			sp_with_offers_price.id as price_id,
			sp.offers_pid as offers_pid,
			sp_price.type_price_id,
			min(sp_price.price) as min_price,
			sp_price.currency_code
		from
			shop_product_price as sp_price
			LEFT JOIN shop_product as sp on sp.id = sp_price.product_id
			LEFT JOIN shop_product as sp_with_offers on sp_with_offers.id = sp.offers_pid
			LEFT JOIN shop_product_price as sp_with_offers_price on sp_with_offers_price.product_id = sp_with_offers.id
		WHERE
			sp.offers_pid is not null
			AND sp_with_offers_price.id is not null
		GROUP BY
			sp.offers_pid,
			sp_price.type_price_id
		ORDER BY
			`sp`.`offers_pid` DESC
	) sp_with_offers_price ON sp_with_offers_price.price_id = price.id
SET
	price.`price` = sp_with_offers_price.min_price,
	price.`currency_code` = sp_with_offers_price.currency_code

SQL
        )->execute();

        return $this;
    }


    /**
     * @param CmsSite|null $cmsSite
     */
    static public function updateProductPrices(CmsSite $cmsSite = null)
    {
        if ($cmsSite === null) {
            $cmsSite = \Yii::$app->skeeks->site;
        }

        $sqlFile = \Yii::getAlias('@skeeks/cms/shop/sql/update-product-from-store-products.sql');
        $sql = file_get_contents($sqlFile);
        $sql = str_replace("{site_id}", $cmsSite->id, $sql);

        /*print_r($sql);die;*/

        \Yii::$app->db->createCommand($sql)->execute();
    }

    /**
     * @param CmsSite|null $cmsSite
     * @deprecated
     */
    static public function importNewProductsOnSite(CmsSite $cmsSite = null)
    {
        return false;

        ini_set("memory_limit", "1024M");

        if ($cmsSite === null) {
            $cmsSite = \Yii::$app->skeeks->site;
        }

        if (!$cmsSite->shopSite) {
            throw new Exception("Сайт не настроен зайдите в основные настройким магазина и заполните недостающие настройки магазина");
        }

        /*if (!$cmsSite->shopSite->catalogCmsTree) {
            throw new Exception("В основных настройках сайта укажите каталог для товаров");
        }*/

        //1) Создаем необходимые категории на сайте
        $data = \Yii::$app->db->createCommand(<<<SQL
        SELECT 
            * 
        FROM 
            (
                /*Выбор раздеов из товаров с предложениями*/
                
                SELECT 
                    offers_tree.* 
                FROM 
                    cms_content_element as ce 
                    LEFT JOIN shop_product as sp ON sp.id = ce.id 
                    
                    LEFT JOIN shop_product as sp_main ON sp_main.id = ce.main_cce_id 
                    
                    LEFT JOIN shop_product as sp_main_with_offers ON sp_main_with_offers.id = sp_main.offers_pid 
                    LEFT JOIN cms_content_element as ce_main_with_offers ON ce_main_with_offers.id = sp_main_with_offers.id 
                    
                    LEFT JOIN cms_tree as offers_tree ON offers_tree.id = ce_main_with_offers.tree_id 
                WHERE 
                    
                    /*Импорт только элементов заданных в настройках сайта*/
                    ce.cms_site_id in (
                        SELECT 
                            shop_import_cms_site.sender_cms_site_id 
                        FROM 
                            shop_import_cms_site 
                        WHERE 
                            shop_import_cms_site.cms_site_id = {$cmsSite->id}
                    ) 
                    /*Только товары которые привязаны к моделям*/
                    AND ce.main_cce_id is not null 
                    AND offers_tree.id is not null 
                    AND sp_main_with_offers.product_type = "offers"
                GROUP BY 
                    offers_tree.id 
                UNION ALL 
                
                /*Выбор раздело из товаров*/
                
                SELECT 
                    tree.* 
                FROM 
                    cms_content_element as ce 
                    LEFT JOIN shop_product as sp ON sp.id = ce.id 
                    
                    LEFT JOIN cms_content_element as ce_main ON ce_main.id = ce.main_cce_id
                    LEFT JOIN shop_product as sp_main ON sp_main.id = ce_main.id 
                    
                    LEFT JOIN cms_tree as tree ON tree.id = ce_main.tree_id 
                WHERE 
                    
                    /*Импорт только элементов заданных в настройках сайта*/
                    ce.cms_site_id in (
                        SELECT 
                            shop_import_cms_site.sender_cms_site_id 
                        FROM 
                            shop_import_cms_site 
                        WHERE 
                            shop_import_cms_site.cms_site_id = {$cmsSite->id}
                    ) 
                    /*Только товары которые привязаны к моделям*/
                    AND ce.main_cce_id is not null 
                    AND tree.id is not null 
                    AND sp_main.product_type = 'simple'
                GROUP BY 
                    tree.id
            ) as all_tree 
        GROUP BY 
            all_tree.id
SQL
        )->queryAll();

        if ($data) {
            foreach ($data as $row) {
                $source = CmsTree::find()->where(['id' => $row['id']])->one();
                $parent = $cmsSite->shopSite->catalogCmsTree;

                if (!CmsTree::find()
                    ->andWhere(['cms_site_id' => $cmsSite->id])
                    ->andWhere(['main_cms_tree_id' => $source->id])
                    ->exists()) {
                    $tree = new CmsTree();
                    $tree->name = $source->name;
                    $tree->main_cms_tree_id = $source->id;

                    if (!$tree->appendTo($parent)->save()) {
                        throw new Exception("Раздел не создан: ".print_r($tree->errors, true));
                    }
                }

            }
        }

        /**
         * 2 вставка товаров
         */

        $sqlFile = \Yii::getAlias('@skeeks/cms/shop/sql/insert-new-products.sql');
        $sql = file_get_contents($sqlFile);
        $sql = str_replace("{site_id}", $cmsSite->id, $sql);
        $sql = str_replace("{limit}", 5000, $sql);

        \Yii::$app->db->createCommand($sql)->execute();

        \Yii::$app->shop->updateOffersPrice();


    }


    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getOfferCmsContentProperties()
    {
        $q = CmsContentProperty::find()
            ->cmsSite()
            ->andWhere(["is_offer_property" => 1]);

        return $q->all();
    }


    /**
     * @return ShopUser
     * @deprecated
     */
    public function getCart()
    {
        return $this->shopUser;
    }

    /**
     * @return ShopUser
     * @deprecated
     */
    public function getShopFuser()
    {
        return $this->shopUser;
    }


    /**
     * Возвращает данные для меню товаров в админке
     * @return array|mixed
     */
    static public function getAdminShopProductsMenu()
    {
        $result = [];

        try {
            $table = \skeeks\cms\models\CmsContent::getTableSchema();
        } catch (\Exception $e) {
            return $result;
        }

        if (\Yii::$app->shop->contentProducts) {
            /**
             * @var $content \skeeks\cms\models\CmsContent
             */
            $content = \Yii::$app->shop->contentProducts;
            $itemData = [
                'label'          => $content->name,
                "img"            => ['\skeeks\cms\shop\assets\Asset', 'icons/e-commerce.png'],
                'url'            => ["shop/admin-cms-content-element", "content_id" => $content->id],
                "activeCallback" => function ($adminMenuItem) use ($content) {
                    return (bool)($content->id == \Yii::$app->request->get("content_id") && \Yii::$app->controller->uniqueId == 'shop/admin-cms-content-element');
                },

                "accessCallback" => function ($adminMenuItem) use ($content) {
                    $permissionNames = "shop/admin-cms-content-element__".$content->id;
                    foreach ([$permissionNames] as $permissionName) {
                        if ($permission = \Yii::$app->authManager->getPermission($permissionName)) {
                            if (!\Yii::$app->user->can($permission->name)) {
                                return false;
                            }
                        }
                    }

                    return true;
                },
            ];

            $result[] = $itemData;
        }

        if (count($result) > 1) {
            return [
                'products' => [
                    'priority' => 260,
                    'label'    => \Yii::t('skeeks/shop/app', 'Goods'),
                    "img"      => ['\skeeks\cms\shop\assets\Asset', 'icons/e-commerce.png'],

                    'items' => $result,
                ],
            ];
        } else {
            if (isset($result[0])) {
                $result[0]['priority'] = 260;
                $result[0]['label'] = "Товары и услуги";
                return ['products' => $result[0]];
            }

            return [];
        }
    }


    /**
     * @return array
     */
    public function getDeliveryHandlersForSelect()
    {
        $result = [];

        if ($this->deliveryHandlers) {
            foreach ($this->deliveryHandlers as $id => $handlerClass) {

                if (is_array($handlerClass)) {
                    $handlerClass = ArrayHelper::getValue($handlerClass, 'class');
                }

                $result[$id] = (new $handlerClass())->descriptor->name;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getCloudkassaHandlersForSelect()
    {
        $result = [];

        if ($this->cloudkassaHandlers) {
            foreach ($this->cloudkassaHandlers as $id => $handlerClass) {

                if (is_array($handlerClass)) {
                    $handlerClass = ArrayHelper::getValue($handlerClass, 'class');
                }

                $result[$id] = (new $handlerClass())->descriptor->name;
            }
        }

        return $result;
    }


    /**
     * @return array
     */
    public function getPaysystemHandlersForSelect()
    {
        $result = [];

        if ($this->paysystemHandlers) {
            foreach ($this->paysystemHandlers as $id => $handlerClass) {
                if (is_array($handlerClass)) {
                    $handlerClass = ArrayHelper::getValue($handlerClass, 'class');
                }

                $result[$id] = (new $handlerClass())->descriptor->name;
            }
        }

        return $result;
    }


    /**
     * @var array
     */
    protected $_stores = null;

    /**
     * @return array
     */
    public function getAllStores()
    {
        $stores = $this->stores;
        return ArrayHelper::merge($stores, $this->supplierStores);
    }

    /**
     * @return ShopStore[]
     */
    public function getStores()
    {
        if ($this->_stores === null) {
            $this->_stores = ShopStore::find()->cmsSite()->andWhere(['is_supplier' => 0])->all();
        }

        return $this->_stores;
    }

    /**
     * @param array $shopStores
     * @return $this
     */
    public function setStores($shopStores = [])
    {
        $this->_stores = $shopStores;
        return $this;
    }


    /**
     * @var array
     */
    protected $_supplierStores = null;


    /**
     * @return ShopStore[]
     */
    public function getSupplierStores()
    {
        if ($this->_supplierStores === null) {
            $this->_supplierStores = ShopStore::find()->cmsSite()->andWhere(['is_supplier' => 1])->all();
        }

        return $this->_supplierStores;
    }

    /**
     * @param array $shopStores
     * @return $this
     */
    public function setSupplierStores($shopStores = [])
    {
        $this->_supplierStores = $shopStores;
        return $this;
    }


    /**
     * @var null|ShopStore
     */
    protected $_shopStore = null;

    /**
     * @return ShopStore|null
     */
    public function getBackendShopStore()
    {
        return $this->_shopStore;
    }

    /**
     * @param ShopStore $shopStore
     * @return $this
     */
    public function setBackendShopStore(ShopStore $shopStore)
    {
        $this->_shopStore = $shopStore;
        return $this;
    }

    /**
     * Получение данных для отправки в js событие
     *
     * @param ShopCmsContentElement $cmsContentElement
     * @return array
     */
    static public function productDataForJsEvent(ShopCmsContentElement $cmsContentElement)
    {

        $price = 0;
        if ($cmsContentElement->shopProduct->minProductPrice) {
            $price = $cmsContentElement->shopProduct->minProductPrice->money->amount;
        } elseif ($cmsContentElement->shopProduct->baseProductPrice) {
            $price = $cmsContentElement->shopProduct->baseProductPrice->money->amount;
        }
        $data = [
            'id'    => $cmsContentElement->id,
            "name"  => $cmsContentElement->seoName,
            "price" => (float)$price,
        ];

        if ($cmsContentElement->cmsTree) {
            $data['category'] = $cmsContentElement->cmsTree->name;
        }

        if ($cmsContentProperty = CmsContentProperty::find()->cmsSite()->andWhere(['is_vendor' => 1])->one()) {
            if ($brandName = $cmsContentElement->relatedPropertiesModel->getAttributeAsText($cmsContentProperty->code)) {
                $data['brand'] = $brandName;
            }
        }

        /*if ($shopCmsContentProperty = \skeeks\cms\shop\models\ShopCmsContentProperty::find()->where(['is_vendor' => 1])->one()) {
            $brandId = $cmsContentElement->relatedPropertiesModel->getAttribute($shopCmsContentProperty->cmsContentProperty->code);
            if ($brandId) {
                if ($brand = \skeeks\cms\models\CmsContentElement::findOne((int)$brandId)) {
                    $data['brand'] = $brand->name;
                }
            }

        }*/

        return $data;

    }

    /**
     * @param ActiveQuery $q
     * @return array
     */
    static public function getAgregateCategoryData(ActiveQuery $q, $model, $filtersData = [], $availableFilter = 0)
    {

        $r = new \ReflectionClass($model);
        $className = $r->getShortName();
        $cacheName = "agregateData_".$className."_".$model->id."_".\Yii::$app->id."_".$availableFilter;

        //Если это неиндексируемая страница с несколькими фильтрами, то нет смысла считать трудозатратные вещи и кэшировать это
        $isUseCache = false;
        if ($model instanceof CmsSavedFilter) {
            $isUseCache = true;
        } else {
            if (!$filtersData) {
                $isUseCache = true;
            }
        }

        if ($isUseCache) {
            //Если данные в кэше берем их оттуда
            if ($result = \Yii::$app->cache->get($cacheName)) {
                return $result;
            }
        }


        try {
            $result = [];


            $q0 = clone $q;

            $realPrice = '';
            $select = [
                \skeeks\cms\models\CmsContentElement::tableName().".id",
            ];
            if (isset($q0->select['realPrice'])) {
                $realPrice = $q0->select['realPrice'];
                $select['realPrice'] = $realPrice;
            }
            $q0->select($select);

            $q0->andWhere([
                'shopProduct.product_type' => [
                    ShopProduct::TYPE_SIMPLE,
                    ShopProduct::TYPE_OFFER,
                ],
            ]);
            $q0->groupBy("shopProduct.id");
            $q0->orderBy(false);

            $result['offerCount'] = $q0->count();

            if ($isUseCache === false) {
                //Если кэш отключен, значит это страница неиндексируемая и другие рассчеты не нужны
                return $result;
            }

            //Если товаров нет, то ничего не делаем
            if (!$result['offerCount']) {
                return [];
            }


            $baseTypePrice = \Yii::$app->shop->baseTypePrice;

            $q1 = clone $q;
            $q1->innerJoin(['prices_for_calc' => 'shop_product_price'], [
                'prices_for_calc.product_id'    => new Expression('shopProduct.id'),
                'prices_for_calc.type_price_id' => $baseTypePrice->id,
            ]);
            $q1->select(['price' => new Expression("max(prices_for_calc.price)")]);
            $q1->groupBy(false);
            $q1->orderBy(false);


            $maxPrice = $q1->asArray()->one();

            if ($maxPrice) {
                $result['highPrice'] = ArrayHelper::getValue($maxPrice, "price");
            }


            $q2 = clone $q;
            $q2->select(['price' => new Expression("min(prices_for_calc.price)")]);
            $q2->innerJoin(['prices_for_calc' => 'shop_product_price'], [
                'prices_for_calc.product_id'    => new Expression('shopProduct.id'),
                'prices_for_calc.type_price_id' => $baseTypePrice->id,
            ]);
            $q2->andWhere(['>', 'prices_for_calc.price', 0]);
            $q2->orderBy(false);
            $q2->groupBy(false);

            $minPrice = $q2->asArray()->one();
            if ($minPrice) {
                $result['lowPrice'] = ArrayHelper::getValue($minPrice, "price");
            }


            $q3 = clone $q;
            $q3->select([
                'id'           => new Expression("shopProduct.id"),
                'rating_count' => new Expression("shopProduct.rating_count"),
            ]);
            $q3->andWhere([
                'shopProduct.product_type' => [
                    ShopProduct::TYPE_SIMPLE,
                    ShopProduct::TYPE_OFFER,
                ],
            ]);
            $q3->groupBy("shopProduct.id");
            $q3->orderBy(false);

            $ratingValueQ = new Query();
            $ratingValueQ->select(['rating_count' => new Expression("sum(rating_count)")])->from(['p' => $q3]);

            $reviewValue = $ratingValueQ->one();
            if ($reviewValue) {
                $result['reviewCount'] = ArrayHelper::getValue($reviewValue, "rating_count", 0);
            }


            if (ArrayHelper::getValue($result, "reviewCount", 0) > 0) {

                $q4 = clone $q;
                $q4->select([
                    'id'           => new Expression("shopProduct.id"),
                    'rating_value' => new Expression("shopProduct.rating_value"),
                ]);
                $q4->andWhere([
                    'shopProduct.product_type' => [
                        ShopProduct::TYPE_SIMPLE,
                        ShopProduct::TYPE_OFFER,
                    ],
                ]);
                $q4->groupBy("shopProduct.id");
                $q4->orderBy(false);

                $ratingValueQ = new Query();
                $ratingValueQ->select(['rating_value' => new Expression("sum(rating_value)")])->from(['p' => $q4]);

                //print_r($q4->createCommand()->rawSql);die;
                $ratingValue = $ratingValueQ->one();
                if ($ratingValue) {
                    $result['ratingValue'] = round(ArrayHelper::getValue($ratingValue, "rating_value", 0) / $result['offerCount'], 4);
                }
            }


            $q5 = clone $q;
            $q5->select(['min_rating' => new Expression("min(shopProduct.rating_value)")]);
            $q5->andWhere([
                'shopProduct.product_type' => [
                    ShopProduct::TYPE_SIMPLE,
                    ShopProduct::TYPE_OFFER,
                ],
            ]);
            $q5->groupBy(false);
            $q5->orderBy(false);

            $minRatingValue = $q5->asArray()->one();
            if ($minRatingValue) {
                $result['worsRating'] = ArrayHelper::getValue($minRatingValue, "min_rating", 0);
            }


            $q6 = clone $q;
            $q6->select(['max_rating' => new Expression("max(shopProduct.rating_value)")]);
            $q6->andWhere([
                'shopProduct.product_type' => [
                    ShopProduct::TYPE_SIMPLE,
                    ShopProduct::TYPE_OFFER,
                ],
            ]);
            $q6->groupBy(false);
            $q6->orderBy(false);

            $minRatingValue = $q6->asArray()->one();
            if ($minRatingValue) {
                $result['bestRating'] = ArrayHelper::getValue($minRatingValue, "max_rating", 0);
            }

            \Yii::$app->cache->set($cacheName, $result, 3600 * 24, new TagDependency([
                'tags' => [
                    \Yii::$app->skeeks->site->cacheTag,
                ],
            ]));
        } catch (\Exception $exception) {

            \Yii::error($exception->getMessage());
            return [];
        }


        return $result;
    }
}