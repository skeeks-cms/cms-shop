INSERT IGNORE
    INTO {{%shop_product_price}} (`product_id`, `type_price_id`, `price`, `currency_code`)
SELECT
    product_id,
    type_price_id,
    if(price is null, 0, ROUND(price)) as price,
    "RUB"
FROM (
    SELECT
        ce.id as product_id,
        price_data.price,
        tp.id as type_price_id
    FROM
        {{%cms_content_element}} as ce
    INNER JOIN {{%shop_product}} as sp on sp.id = ce.id

    INNER JOIN (
        SELECT
        tp_inner.id as id
        FROM
        {{%shop_type_price}} as tp_inner
        WHERE tp_inner.is_default = 1 AND tp_inner.cms_site_id = :site_id
    ) as tp

    INNER JOIN (

        SELECT
            (if(store.source_purchase_price = 'selling_price', ssp.selling_price, ssp.purchase_price) * store.selling_extra_charge / 100) as price,
            (
                IF(
                    ssp.quantity > 0,
                    0,
                    1
                )
            ) AS is_quantity,
            ssp.shop_product_id
        FROM
            {{%shop_store_product}} as ssp
            INNER JOIN {{%shop_store}} as store ON ssp.shop_store_id = store.id AND ssp.is_active = 1 AND store.is_active = 1
        WHERE
            store.cms_site_id = :site_id
            AND (store.is_supplier = 1 || store.is_sync_external = 1)

        ORDER BY
            is_quantity DESC,
            store.priority

    )  as price_data ON price_data.shop_product_id = ce.id

    WHERE
        ce.cms_site_id = :site_id
        AND ce.id BETWEEN :from_id AND :to_id

    GROUP BY ce.id

) as q
