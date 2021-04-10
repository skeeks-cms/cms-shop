UPDATE
	`shop_product_price` as price
	INNER JOIN (
        SELECT
            receiver_product_id,
            receiver_price_id,
            ROUND(calc_price) as calc_price_round,
            sender_currency_code
        FROM (
            SELECT
                siteimport.extra_charge,
                siteimport.priority,
                siteimport.sender_cms_site_id,
                siteimport.sender_shop_type_price_id,
                sprice.id as sender_price_id,
                sprice.product_id as sender_product_id,
                sprice.type_price_id as sender_type_price_id,
                sprice.price as sender_price,
                (sprice.price * siteimport.extra_charge / 100) as calc_price,
                sprice.currency_code as sender_currency_code,
                sp_for_import.id as receiver_product_id,
                (
                    SELECT
                        id
                    FROM
                        shop_product_price as spp
                    WHERE
                        spp.product_id = sp_for_import.id
                        AND spp.is_fixed != 1
                        AND spp.type_price_id = (
                            SELECT
                                id
                            FROM
                                shop_type_price as stp
                            WHERE
                                stp.cms_site_id = 5
                                AND is_default = 1
                        )
                ) as receiver_price_id
            FROM
                shop_product_price as sprice
                /*берем все цены*/
                LEFT JOIN shop_product as sp on sp.id = sprice.product_id
                /*товары связанные с ценами*/
                LEFT JOIN cms_content_element as cce on cce.id = sp.id
                /*элементы контента к этим товарам*/
                INNER JOIN shop_product as main_sp on main_sp.id = cce.main_cce_id
                /*все это ищем только в главных товарах*/
                LEFT JOIN (

                    /*задания на импорт для этого сайта*/
                    SELECT
                        shop_import_cms_site.*
                    FROM
                        shop_import_cms_site
                    WHERE
                        shop_import_cms_site.cms_site_id = 5
                ) as siteimport on siteimport.sender_cms_site_id = cce.cms_site_id
                /*привязываем задание на импорт к каждому элементу контента*/
                LEFT JOIN (
                    SELECT
                        inner_sp.*,
                        inner_cce.main_cce_id as main_cce_id
                    FROM
                        shop_product as inner_sp
                        LEFT JOIN cms_content_element as inner_cce on inner_cce.id = inner_sp.id
                    WHERE
                        inner_cce.cms_site_id = 5
                ) as sp_for_import on sp_for_import.main_cce_id = cce.main_cce_id
                /*Привязать товары сайта на который будет идти загрузка цен*/
            WHERE
                cce.cms_site_id IN (
                    SELECT
                        shop_import_cms_site.sender_cms_site_id
                    FROM
                        shop_import_cms_site
                    WHERE
                        shop_import_cms_site.cms_site_id = 5
                )
                and sprice.type_price_id IN (
                    SELECT
                        shop_import_cms_site.sender_shop_type_price_id
                    FROM
                        shop_import_cms_site
                    WHERE
                        shop_import_cms_site.cms_site_id = 5
                )
                and sp_for_import.id is not null
            /*GROUP BY
                main_sp.id*/
            ORDER BY
                siteimport.priority ASC

        ) as q
        GROUP BY receiver_product_id

	) calc_price ON calc_price.receiver_price_id = price.id
SET
	price.`price` = calc_price.calc_price_round,
	price.`currency_code` = calc_price.sender_currency_code
WHERE calc_price.receiver_price_id is not null
;