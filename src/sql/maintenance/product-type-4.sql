            UPDATE
                {{%cms_content_element}} as cce
                INNER JOIN
                (
                    /*Товары которые являются предложениями */
                   SELECT
                       inner_sp.id as inner_sp_id,
                        inner_sp.offers_pid,
                        inner_sp.product_type
                   FROM {{%shop_product}} inner_sp
                   WHERE inner_sp.offers_pid is not null
                   GROUP BY inner_sp.id
                ) sp_has_parent ON cce.id = sp_has_parent.inner_sp_id
                LEFT JOIN {{%shop_product}} as offers_sp on offers_sp.id = sp_has_parent.offers_pid
                LEFT JOIN {{%cms_content_element}} as offers_cce on offers_cce.id = offers_sp.id
            SET
                cce.`tree_id` = offers_cce.tree_id
WHERE cce.id BETWEEN :from_id AND :to_id
