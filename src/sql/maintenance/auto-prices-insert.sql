INSERT IGNORE
    INTO {{%shop_product_price}} (`product_id`, `type_price_id`, `price`, `currency_code`)
    SELECT
        spp.product_id,
        :type_id,
        ROUND(spp.price * :extra / 100),
        spp.currency_code
    FROM
        {{%shop_product_price}} as spp
    WHERE
        spp.type_price_id = :base_id
        AND spp.product_id BETWEEN :from_id AND :to_id
