SET @site_id = {site_id};
/**
 * Создание недостающих закупочных цен
 */
INSERT IGNORE
    INTO shop_product_price (`created_at`,`updated_at`,`product_id`, `type_price_id`, `price`, `currency_code`)
SELECT
    UNIX_TIMESTAMP(),
    UNIX_TIMESTAMP(),
    product_id,
    type_price_id,
    if(purchase_price is null, 0, purchase_price) as purchase_price,
    "RUB"
FROM (
    SELECT
        ce.id as product_id,
        (
            SELECT
                ssp.purchase_price
            FROM
                shop_store_product as ssp
                INNER JOIN shop_store as store ON ssp.shop_store_id = store.id
            WHERE
                store.cms_site_id = @site_id
                AND store.is_supplier = 1
                AND ssp.shop_product_id = ce.id
                /*AND ssp.purchase_price > 0*/
            ORDER BY
                store.priority
            LIMIT 1

        ) as purchase_price,
    	tp.id as type_price_id
    FROM
        cms_content_element as ce
    INNER JOIN shop_product as sp on sp.id = ce.id
    INNER JOIN (
        SELECT
        tp_inner.id as id
        FROM
        shop_type_price as tp_inner
        WHERE tp_inner.is_purchase = 1 AND tp_inner.cms_site_id = @site_id
    ) as tp
    WHERE
        ce.cms_site_id = @site_id
) as q
;



/**
 * Обновление недостающих закупочных цен
 */
UPDATE
	`shop_product_price` as spp_update
    INNER JOIN (
        SELECT
            subq.id,
            if(subq.purchase_price is null, 0, subq.purchase_price) as purchase_price
        FROM
        (SELECT
                spp.id,
                (
                    SELECT
                        if(ssp.purchase_price is null, 0, ssp.purchase_price) as purchase_price
                    FROM
                        shop_store_product as ssp
                        INNER JOIN shop_store as store ON ssp.shop_store_id = store.id
                    WHERE
                        ssp.shop_product_id = spp.product_id
                        AND store.is_supplier = 1
                        /*AND ssp.purchase_price > 0*/
                    ORDER BY
                        store.priority
                    LIMIT 1
                ) as purchase_price
            FROM
                `shop_product_price` as spp
            INNER JOIN (
                SELECT
                    tp_inner.id as id
                FROM
                    shop_type_price as tp_inner
                WHERE tp_inner.is_purchase = 1 AND tp_inner.cms_site_id = @site_id
            ) as tp ON spp.type_price_id = tp.id
        ) as subq
    ) as inner_spp ON inner_spp.id = spp_update.id
SET
    spp_update.price = inner_spp.purchase_price
WHERE
    spp_update.is_fixed = 0
    AND inner_spp.purchase_price > 0
;




/**
 * Создание недостающих розничных цен
 */
INSERT IGNORE
    INTO shop_product_price (`created_at`,`updated_at`,`product_id`, `type_price_id`, `price`, `currency_code`)
SELECT
    UNIX_TIMESTAMP(),
    UNIX_TIMESTAMP(),
    product_id,
    type_price_id,
    if(price is null, 0, price) as price,
    "RUB"
FROM (
    SELECT
        ce.id as product_id,
        (
            SELECT
                ssp.selling_price
            FROM
                shop_store_product as ssp
                INNER JOIN shop_store as store ON ssp.shop_store_id = store.id
            WHERE
                store.cms_site_id = @site_id
                AND store.is_supplier = 1
                AND ssp.shop_product_id = ce.id
                /*AND ssp.purchase_price > 0*/
            ORDER BY
                store.priority
            LIMIT 1

        ) as price,
    	tp.id as type_price_id
    FROM
        cms_content_element as ce
    INNER JOIN shop_product as sp on sp.id = ce.id
    INNER JOIN (
        SELECT
        tp_inner.id as id
        FROM
        shop_type_price as tp_inner
        WHERE tp_inner.is_default = 1 AND tp_inner.cms_site_id = @site_id
    ) as tp
    WHERE
        ce.cms_site_id = @site_id
) as q
;



/**
 * Обновление розничных цен
 */
UPDATE
	`shop_product_price` as spp_update
    INNER JOIN (
        SELECT
            subq.id,
            if(subq.price is null, 0, subq.price) as price
        FROM
        (SELECT
                spp.id,
                (
                    SELECT
                        if(ssp.selling_price is null, 0, ssp.selling_price) as selling_price
                    FROM
                        shop_store_product as ssp
                        INNER JOIN shop_store as store ON ssp.shop_store_id = store.id
                    WHERE
                        ssp.shop_product_id = spp.product_id
                        AND store.is_supplier = 1
                        /*AND ssp.purchase_price > 0*/
                    ORDER BY
                        store.priority
                    LIMIT 1
                ) as price
            FROM
                `shop_product_price` as spp
            INNER JOIN (
                SELECT
                    tp_inner.id as id
                FROM
                    shop_type_price as tp_inner
                WHERE tp_inner.is_default = 1 AND tp_inner.cms_site_id = @site_id
            ) as tp ON spp.type_price_id = tp.id
        ) as subq
    ) as inner_spp ON inner_spp.id = spp_update.id
SET
    spp_update.price = inner_spp.price
WHERE
    spp_update.is_fixed = 0
    AND inner_spp.price > 0
;


