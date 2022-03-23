START TRANSACTION;

SET AUTOCOMMIT = 0;

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
    if(price is null, 0, price) as price,
    "RUB"
FROM (
    SELECT
        ce.id as product_id,
        price_data.price,
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

        INNER JOIN (

            SELECT
                (if(store.source_purchase_price = 'purchase_price', ssp.purchase_price, ssp.selling_price) * store.purchase_extra_charge / 100) as price,
                (
                    IF(
                        ssp.quantity > 0,
                        0,
                        1
                    )
                ) AS is_quantity,
                ssp.shop_product_id
            FROM
                shop_store_product as ssp
                INNER JOIN shop_store as store ON ssp.shop_store_id = store.id
            WHERE
                store.cms_site_id = @site_id
                AND store.is_supplier = 1
                /*AND ssp.purchase_price > 0*/
            ORDER BY
                is_quantity,
                store.priority

        )  as price_data ON price_data.shop_product_id = ce.id

    WHERE
        ce.cms_site_id = @site_id

    GROUP BY ce.id
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
            if(subq.price is null, 0, subq.price) as price
        FROM
        (SELECT
                spp.id,
                spp.is_fixed,
                price_data.price
            FROM
                `shop_product_price` as spp
            INNER JOIN (
                SELECT
                    tp_inner.id as id
                FROM
                    shop_type_price as tp_inner
                WHERE tp_inner.is_purchase = 1 AND tp_inner.cms_site_id = @site_id
            ) as tp ON spp.type_price_id = tp.id


            INNER JOIN (
                SELECT
                    /*if(ssp.selling_price is null, 0, ssp.selling_price) as selling_price*/
                    (if(store.source_selling_price = 'purchase_price', ssp.selling_price, ssp.purchase_price) * store.selling_extra_charge / 100) as price,
                    (
                        IF(
                            ssp.quantity > 0,
                            0,
                            1
                        )
                    ) AS is_quantity,
                    ssp.shop_product_id
                FROM
                    shop_store_product as ssp
                    INNER JOIN shop_store as store ON ssp.shop_store_id = store.id
                WHERE
                    store.cms_site_id = @site_id
                    AND store.is_supplier = 1
                ORDER BY
                    is_quantity,
                    store.priority
            ) as price_data ON price_data.shop_product_id = spp.product_id

        ) as subq
        WHERE
            subq.is_fixed = 0
            AND subq.price > 0
        GROUP BY subq.id
    ) as inner_spp ON inner_spp.id = spp_update.id
SET
    spp_update.price = inner_spp.price
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
        price_data.price,
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

    INNER JOIN (

        SELECT
            (if(store.source_purchase_price = 'selling_price', ssp.purchase_price, ssp.selling_price) * store.purchase_extra_charge / 100) as price,
            (
                IF(
                    ssp.quantity > 0,
                    0,
                    1
                )
            ) AS is_quantity,
            ssp.shop_product_id
        FROM
            shop_store_product as ssp
            INNER JOIN shop_store as store ON ssp.shop_store_id = store.id
        WHERE
            store.cms_site_id = @site_id
            AND store.is_supplier = 1
            /*AND ssp.purchase_price > 0*/
        ORDER BY
            is_quantity,
            store.priority

    )  as price_data ON price_data.shop_product_id = ce.id

    WHERE
        ce.cms_site_id = @site_id

    GROUP BY ce.id

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
                spp.is_fixed,
                price_data.price
            FROM
                `shop_product_price` as spp
            INNER JOIN (
                SELECT
                    tp_inner.id as id
                FROM
                    shop_type_price as tp_inner
                WHERE tp_inner.is_default = 1 AND tp_inner.cms_site_id = @site_id
            ) as tp ON spp.type_price_id = tp.id

            INNER JOIN (
                SELECT
                    /*if(ssp.selling_price is null, 0, ssp.selling_price) as selling_price*/
                    (if(store.source_selling_price = 'selling_price', ssp.selling_price, ssp.purchase_price) * store.selling_extra_charge / 100) as price,
                    (
                        IF(
                            ssp.quantity > 0,
                            0,
                            1
                        )
                    ) AS is_quantity,
                    ssp.shop_product_id
                FROM
                    shop_store_product as ssp
                    INNER JOIN shop_store as store ON ssp.shop_store_id = store.id
                WHERE
                    store.cms_site_id = @site_id
                    AND store.is_supplier = 1
                ORDER BY
                    is_quantity,
                    store.priority
            ) as price_data ON price_data.shop_product_id = spp.product_id
        ) as subq
        WHERE
            subq.is_fixed = 0
            AND subq.price > 0
        GROUP BY subq.id
    ) as inner_spp ON inner_spp.id = spp_update.id
SET
    spp_update.price = inner_spp.price
;

COMMIT;


