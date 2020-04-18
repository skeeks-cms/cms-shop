START TRANSACTION;

SET AUTOCOMMIT = 0;

SET @site_id = 10;

/* Вставка элементов контента */
INSERT IGNORE
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
	/*Только товары которые еще не добавлены на сайт*/
	/*AND sp.main_pid not in (
	    SELECT
	        added_sp.main_pid
        FROM
            shop_product as added_sp
            LEFT JOIN cms_content_element as ce_added ON ce_added.id = added_sp.id
        WHERE
            ce_added.cms_site_id = @site_id
	)*/
GROUP BY
	sp_main.id
LIMIT 20;


/* Вставка товаров */
INSERT
    INTO shop_product (`id`,`main_pid`,`product_type`, `measure_matches_jsondata`, `measure_ratio`, `measure_code`, `width`, `length`, `height`, `weight`, `quantity`)
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
    sp_model.weight,
    (
        SELECT SUM(sp_inner.quantity) as sum_quantity
           FROM shop_product as sp_inner
           LEFT JOIN cms_content_element as cce_inner ON cce_inner.id = sp_inner.id
           WHERE cce_inner.cms_site_id in (
                SELECT
                    shop_import_cms_site.sender_cms_site_id
                FROM
                    shop_import_cms_site
                WHERE
                    shop_import_cms_site.cms_site_id = @site_id
           ) AND sp_inner.main_pid = sp_model.id
        GROUP BY sp_inner.main_pid
    )
FROM
    cms_content_element as cce
    LEFT JOIN shop_product as sp ON sp.id = cce.id
    LEFT JOIN shop_product as sp_model ON sp_model.id = cce.external_id
WHERE
    sp.id is null AND
    cce.cms_site_id = @site_id;



COMMIT;









