UPDATE
    {{%shop_product_price}} as update_price
    INNER JOIN (
        SELECT
            spp.id,
            spp.currency_code,
            UNIX_TIMESTAMP() as updated_at_now,
            (
                SELECT
                    ROUND(
                        calc_price.price * stp.auto_extra_charge / 100
                    )
                FROM
                    {{%shop_product_price}} as calc_price
                WHERE
                    calc_price.product_id = spp.product_id
                    AND calc_price.type_price_id = stp.base_auto_shop_type_price_id
            ) as new_price,
            spp.price as old_price
        FROM
            {{%shop_product_price}} as spp
            INNER JOIN (
                SELECT
                    *
                FROM
                    {{%shop_type_price}} as inner_stp
                WHERE
                    inner_stp.is_auto = 1
            ) as stp ON stp.id = spp.type_price_id
            LEFT JOIN {{%shop_type_price}} as baseTypePrice on baseTypePrice.id = stp.base_auto_shop_type_price_id
    ) as calced_price ON calced_price.id = update_price.id
SET
    update_price.price = calced_price.new_price
WHERE update_price.id BETWEEN :from_id AND :to_id
