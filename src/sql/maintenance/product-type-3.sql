            UPDATE
                {{%shop_product}} as sp
                INNER JOIN
                (
                    /*Товары которые являются предложениями */
                   SELECT inner_sp.id as inner_sp_id
                   FROM {{%shop_product}} inner_sp
                   WHERE inner_sp.offers_pid is not null
                   AND inner_sp.product_type != 'offer'
                   GROUP BY inner_sp.id
                ) sp_has_parent ON sp.id = sp_has_parent.inner_sp_id
            SET
                sp.`product_type` = "offer"
WHERE sp.id BETWEEN :from_id AND :to_id
