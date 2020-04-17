START TRANSACTION;

SET AUTOCOMMIT = 0;
SET @site_id = 10;

/*Вставка элемента контента*/
INSERT
    INTO cms_content_element (`name`,`code`,`content_id`, `external_id`, `cms_site_id`, `published_at`)
SELECT
	ce_main.name,
	ce_main.code,
	ce_main.content_id,
	ce_main.id,
	@site_id,
	UNIX_TIMESTAMP()
FROM
	cms_content_element as ce
	LEFT JOIN shop_product as sp ON sp.id = ce.id
	LEFT JOIN shop_product as sp_main ON sp_main.id = sp.main_pid
    LEFT JOIN cms_content_element as ce_main ON sp_main.id = ce_main.id
WHERE
	ce.cms_site_id in (
		SELECT
			shop_site_import.cms_site_supplier_id
		FROM
			shop_site_import
		WHERE
			shop_site_import.cms_site_id = @site_id
	)
	AND sp.main_pid is not null
	AND sp.main_pid not in (
	    SELECT
	        added_sp.main_pid
        FROM
            shop_product as added_sp
            LEFT JOIN cms_content_element as ce_added ON ce_added.id = added_sp.id
        WHERE
            ce_added.cms_site_id = @site_id
	)
GROUP BY
	sp_main.id
LIMIT 1;


SET @element_id = LAST_INSERT_ID();

/*Вставка товара*/
INSERT
    INTO shop_product (`id`,`main_pid`,`product_type`, `measure_matches_jsondata`, `measure_ratio`, `measure_code`, `width`, `length`, `height`, `weight`)
SELECT
    cce.id,
    sp_model.id,
    'simple',
    sp_model.measure_matches_jsondata,
    sp_model.measure_ratio,
    sp_model.measure_code,
    sp_model.width,
    sp_model.length,
    sp_model.height,
    sp_model.weight
FROM
    cms_content_element as cce
    LEFT JOIN shop_product as sp_model ON sp_model.id = cce.external_id
WHERE
    cce.id = @element_id;





INSERT
    INTO shop_product (`product_id`, `type_price_id`, `price`, `currency_code`)
SELECT
    @element_id,
    sp_model.id,
    'simple',
    sp_model.measure_matches_jsondata,
    sp_model.measure_ratio,
    sp_model.measure_code,
    sp_model.width,
    sp_model.length,
    sp_model.height,
    sp_model.weight
FROM
    shop_product_price as price
    LEFT JOIN shop_product as sp ON sp.id = price.product_id
    LEFT JOIN cms_content_element as cce ON cce.id = sp.id
WHERE
    sp.main_pid = (
		SELECT
			tmp.`main_pid`
		FROM
			`shop_product` as tmp
		WHERE
			tmp.`id` = @element_id
	)
    AND cce.cms_site_id in (
		SELECT
			shop_site_import.cms_site_supplier_id
		FROM
			shop_site_import
		WHERE
			shop_site_import.cms_site_id = @site_id
	)
LIMIT 1

COMMIT;









