            UPDATE
                {{%shop_product}} as sp
                INNER JOIN
                (
                    SELECT
                        inner_sp.*
                    FROM
                        {{%shop_product}} as inner_sp
                        LEFT JOIN {{%shop_product}} as sp_offers ON sp_offers.offers_pid = inner_sp.id
                    WHERE
                        sp_offers.id is not null /*к товару кто то привязан*/
                        AND inner_sp.product_type != 'offers' /*И у которого неправильный тип*/
                    GROUP BY
                        inner_sp.id
                ) sp_has_parent ON sp.id = sp_has_parent.id
            SET
                sp.`product_type` = "offers"
WHERE sp.id BETWEEN :from_id AND :to_id
