            UPDATE
                {{%shop_product}} as sp
                INNER JOIN (
                    SELECT
                        inner_sp.id
                    FROM
                        {{%shop_product}} as inner_sp
                        LEFT JOIN {{%shop_product}} as sp_offers ON sp_offers.offers_pid = inner_sp.id
                    WHERE
                        inner_sp.offers_pid is null /*не задан общий товар*/
                        and sp_offers.id is null /*к товару никто не привязан*/
                        and inner_sp.product_type != 'simple' /*Не простой товар*/
                        GROUP BY inner_sp.id
                ) as join_sp on join_sp.id = sp.id
            SET
                sp.`product_type` = "simple"
WHERE sp.id BETWEEN :from_id AND :to_id
