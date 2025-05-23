SELECT *
FROM
(
SELECT
        inner_spp.id,
        inner_spp.store_name,
        inner_spp.product_id,
        inner_spp.price,
        inner_spp.is_quantity,
        inner_spp.priority,
        inner_spp.spp_quantity,
        inner_spp.calc_priority,
        inner_spp.spp_id
    FROM
        `shop_product_price` as spp_update
    INNER JOIN (
        SELECT
            subq.store_name,
            subq.product_id,
            subq.id,
            if(subq.price is null, 0, ROUND(subq.price)) as price,
            subq.is_quantity,
            subq.priority,
            subq.spp_quantity,
            subq.calc_priority,
            subq.spp_id
        FROM
        (SELECT
                spp.id,
                spp.is_fixed,
         		spp.product_id,
                price_data.price,
         		price_data.is_quantity,
                price_data.priority,
                price_data.store_name,
                price_data.spp_quantity,
                price_data.calc_priority,
                price_data.spp_id
            FROM
                `shop_product_price` as spp
            INNER JOIN (
                SELECT
                    tp_inner.id as id
                FROM
                    shop_type_price as tp_inner
                WHERE tp_inner.is_default = 1 AND tp_inner.cms_site_id = 216
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
                    (
                        IF(
                            ssp.quantity = 0,
                            100000,
                            0
                        ) + store.priority
                    ) AS calc_priority,
                    /*(min(
                        IF(
                            ssp.quantity = 0,
                            100000,
                            0
                        ) + store.priority
                    )) AS min_calc_priority,*/
                    ssp.shop_product_id,
                	store.name as store_name,
                	store.priority,
                	ssp.id as spp_id,
                	ssp.quantity as spp_quantity
                FROM
                    shop_store_product as ssp
                    INNER JOIN shop_store as store ON ssp.shop_store_id = store.id
                WHERE
                    store.cms_site_id = 216
                    AND (store.is_supplier = 1 || store.is_sync_external = 1)


                /*ORDER BY
                    calc_priority*/
            ) as price_data ON price_data.shop_product_id = spp.product_id
        ) as subq
        WHERE
            subq.is_fixed = 0
            AND subq.price > 0
            AND subq.product_id = 4300466
        /*GROUP BY subq.id*/
        /*ORDER BY
                subq.calc_priority*/


    ) as inner_spp ON inner_spp.id = spp_update.id
    /*GROUP BY inner_spp.id*/
    ORDER BY
        inner_spp.calc_priority
        ) as query
    /*GROUP BY query.id*/
