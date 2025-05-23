SET @site_id = 216;
SELECT
            subq.id,
            if(subq.price is null, 0, ROUND(subq.price)) as price,
            (
                SELECT price FROM
                (
                    SELECT
                        /*if(ssp.selling_price is null, 0, ssp.selling_price) as selling_price*/
                        (if(store_select.source_selling_price = 'selling_price', ssp_select.selling_price, ssp_select.purchase_price) * store_select.selling_extra_charge / 100) as price,
                        (
                            IF(
                                ssp_select.quantity > 0,
                                0,
                                1
                            )
                        ) AS is_quantity,
                        ssp_select.shop_product_id,
                        store_select.priority
                    FROM
                        shop_store_product as ssp_select
                        INNER JOIN shop_store as store_select ON ssp_select.shop_store_id = store_select.id
                    WHERE
                        store_select.cms_site_id = @site_id
                        AND (store_select.is_supplier = 1 || store_select.is_sync_external = 1)
                        /*AND ssp_select.shop_product_id = subq.product_id*/

                ) as inner_calc_price
                WHERE inner_calc_price.shop_product_id = subq.product_id
                ORDER BY
                        inner_calc_price.is_quantity,
                        inner_calc_price.priority
                    LIMIT 1
            ) as calc_price
        FROM
        (
            SELECT
                spp.id,
                spp.type_price_id,
                spp.product_id,
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
                    (if(store.source_selling_price = 'selling_price', ssp_join.selling_price, ssp_join.purchase_price) * store.selling_extra_charge / 100) as price,
                    (
                        IF(
                            ssp_join.quantity > 0,
                            0,
                            1
                        )
                    ) AS is_quantity,
                    ssp_join.shop_product_id as product_id
                FROM
                    shop_store_product as ssp_join
                    INNER JOIN shop_store as store ON ssp_join.shop_store_id = store.id
                WHERE
                    store.cms_site_id = @site_id
                    AND (store.is_supplier = 1 || store.is_sync_external = 1)
                ORDER BY
                    is_quantity DESC,
                    store.priority
            ) as price_data ON price_data.product_id = spp.product_id
        ) as subq
        WHERE
            subq.is_fixed = 0
            AND subq.price > 0
            AND subq.product_id = 4300466
        GROUP BY subq.id