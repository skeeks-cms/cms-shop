UPDATE
    {{%shop_product}} as update_product
    INNER JOIN (
        SELECT
            sp.id,
            FLOOR(shop_site_settings.generate_min_product_rating_count + RAND() * (shop_site_settings.generate_max_product_rating_count - shop_site_settings.generate_min_product_rating_count)) as calc_rating_count,
            ROUND(FLOOR(shop_site_settings.generate_min_product_rating_value + RAND() * (shop_site_settings.generate_max_product_rating_value - shop_site_settings.generate_min_product_rating_value)) + RAND(), 4)  as calc_rating_value
        FROM
            {{%shop_product}} as sp
            INNER JOIN {{%cms_content_element}} as cce on cce.id = sp.id
            INNER JOIN {{%shop_site}} as shop_site_settings on shop_site_settings.id = cce.cms_site_id
        WHERE
                shop_site_settings.is_generate_product_rating = 1
            AND
                sp.rating_count = 0
            AND
                sp.rating_value = 0
    ) as result_sp ON result_sp.id = update_product.id
SET
    update_product.rating_count = result_sp.calc_rating_count,
    update_product.rating_value = result_sp.calc_rating_value
WHERE update_product.id BETWEEN :from_id AND :to_id
