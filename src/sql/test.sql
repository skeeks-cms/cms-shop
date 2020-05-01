
/*Создание общих товаров*/
SELECT
	ce_main_with_offers.name,
	ce_main_with_offers.code,
	ce_main_with_offers.content_id,
	ce_main_with_offers.id,
	sp_main_with_offers.product_type,

	/*source_tree.id as source_tree_id,
		source_tree.name as source_tree_name,*/
	new_tree.id as new_tree_id,
	4,
	UNIX_TIMESTAMP()
FROM

	/* Товары */
	cms_content_element as ce
	LEFT JOIN shop_product as sp ON sp.id = ce.id
	/* Модели */
	LEFT JOIN shop_product as sp_main ON sp_main.id = sp.main_pid
	LEFT JOIN cms_content_element as ce_main ON sp_main.id = ce_main.id
	LEFT JOIN shop_product as sp_main_with_offers ON sp_main_with_offers.id = sp_main.offers_pid
	LEFT JOIN cms_content_element as ce_main_with_offers ON ce_main_with_offers.id = sp_main_with_offers.id
	LEFT JOIN cms_tree as source_tree ON source_tree.id = ce_main_with_offers.tree_id
	LEFT JOIN (
		SELECT
			*
		FROM
			cms_tree as inner_new_tree
		WHERE
			inner_new_tree.cms_site_id = 4
	) as new_tree ON new_tree.external_id = source_tree.id
WHERE
	ce.cms_site_id = 4
	AND sp_main.offers_pid IS NOT NULL






SELECT
	*
FROM
	(
		(
			SELECT
				ce_main.name,
				ce_main.code,
				ce_main.content_id,
				ce_main.id,

				/*source_tree.id as source_tree_id,
									source_tree.name as source_tree_name,*/
				new_tree.id as new_tree_id,
				@site_id,
				UNIX_TIMESTAMP()
			FROM

				/* Товары */
				cms_content_element as ce
				LEFT JOIN shop_product as sp ON sp.id = ce.id
				/* Модели */
				LEFT JOIN shop_product as sp_main ON sp_main.id = sp.main_pid
				LEFT JOIN cms_content_element as ce_main ON sp_main.id = ce_main.id
				LEFT JOIN cms_tree as source_tree ON source_tree.id = ce_main.tree_id
				/* Разделы товаров на новом сайте */
				LEFT JOIN (
					SELECT
						*
					FROM
						cms_tree as inner_new_tree
					WHERE
						inner_new_tree.cms_site_id = @site_id
				) as new_tree ON new_tree.external_id = source_tree.id
			WHERE

				/*Импорт только элементов заданных в настройках сайта*/
				ce.cms_site_id in (
					SELECT
						shop_import_cms_site.sender_cms_site_id
					FROM
						shop_import_cms_site
					WHERE
						shop_import_cms_site.cms_site_id = @site_id
				)
				/*Только товары которые привязаны к моделям*/
				AND sp.main_pid is not null
			GROUP BY
				sp_main.id
		)
		UNION ALL
			(
				SELECT
					ce_main_with_offers.name,
					ce_main_with_offers.code,
					ce_main_with_offers.content_id,
					ce_main_with_offers.id,
					new_tree.id as new_tree_id,
					@site_id,
					UNIX_TIMESTAMP()
				FROM

					/* Товары */
					cms_content_element as ce
					LEFT JOIN shop_product as sp ON sp.id = ce.id
					/* Модели */
					LEFT JOIN shop_product as sp_main ON sp_main.id = sp.main_pid
					LEFT JOIN cms_content_element as ce_main ON sp_main.id = ce_main.id
					/* Общие модели */
					LEFT JOIN shop_product as sp_main_with_offers ON sp_main_with_offers.id = sp_main.offers_pid
					LEFT JOIN cms_content_element as ce_main_with_offers ON ce_main_with_offers.id = sp_main_with_offers.id
					/* Разделы моделей */
					LEFT JOIN cms_tree as source_tree ON source_tree.id = ce_main_with_offers.tree_id
					/* Разделы товаров на новом сайте */
					LEFT JOIN (
						SELECT
							*
						FROM
							cms_tree as inner_new_tree
						WHERE
							inner_new_tree.cms_site_id = @site_id
					) as new_tree ON new_tree.external_id = source_tree.id
				WHERE

					/*Импорт только элементов заданных в настройках сайта*/
					ce.cms_site_id in (
						SELECT
							shop_import_cms_site.sender_cms_site_id
						FROM
							shop_import_cms_site
						WHERE
							shop_import_cms_site.cms_site_id = @site_id
					)
					/*Только товары которые привязаны к моделям*/
					AND sp.main_pid is not null
					AND ce_main_with_offers.id is not null
					/*Только товары которые еще не добавлены на сайт*/
				GROUP BY
					ce_main_with_offers.id
			)
	) as all_elements
GROUP BY
	all_elements.id
LIMIT
	10




INSERT IGNORE
    INTO shop_product_price (`created_at`,`updated_at`,`product_id`, `type_price_id`, `price`, `currency_code`)
select
	/*sp.offers_pid,
    sp_price.type_price_id,
    min(sp_price.price) as min_price,
    sp_price.**/

    UNIX_TIMESTAMP(),
    UNIX_TIMESTAMP(),
    sp.offers_pid,
    sp_price.type_price_id,
    min(sp_price.price) as min_price,
    sp_price.currency_code
from
	shop_product_price as sp_price
	LEFT JOIN shop_product as sp on sp.id = sp_price.product_id
	LEFT JOIN shop_product as sp_with_offers on sp_with_offers.id = sp.offers_pid
WHERE
	sp.offers_pid is not null
GROUP BY
	sp.offers_pid,
	sp_price.type_price_id
ORDER BY `sp`.`offers_pid`  DESC



/*Создание недостающих цен у товаров с пердложениями*/
INSERT IGNORE
    INTO shop_product_price (`created_at`,`updated_at`,`product_id`, `type_price_id`, `price`, `currency_code`)
SELECT
    UNIX_TIMESTAMP(),
    UNIX_TIMESTAMP(),
    sp_with_offers.id,
    (
        SELECT
            id
        FROM
            shop_type_price as stp
        WHERE
            stp.cms_site_id = cce.cms_site_id
            AND is_default = 1
    ) as receiver_type_price_id,
    0,
    "RUB"
FROM
	`shop_product` as sp
	LEFT JOIN shop_product as sp_with_offers ON sp_with_offers.id = sp.offers_pid
	LEFT JOIN cms_content_element as cce on cce.id = sp_with_offers.id
    LEFT JOIN (
    	SELECT
            *
        FROM
            shop_product_price as spp
        WHERE
        spp.type_price_id in (
            SELECT id FROM shop_type_price as stp WHERE stp.is_default = 1
        )
    ) as spprice ON spprice.product_id = sp_with_offers.id

WHERE
	sp.offers_pid is not null
	AND spprice.id is null