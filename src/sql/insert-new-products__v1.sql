START TRANSACTION;

SET AUTOCOMMIT = 0;

SET @site_id = {site_id};


/* Вставка элементов контента */
INSERT IGNORE
    INTO cms_content_element (`name`,`code`,`content_id`, `external_id`, `tree_id`, `cms_site_id`, `published_at`)
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
    /* Общие модели */
    /*LEFT JOIN cms_content_element as ce_main_parent ON ce_main.parent_content_element_id = ce_main_parent.id
    LEFT JOIN shop_product as sp_main_parent ON sp_main_parent.id = ce_main_parent.id*/
    /* Разделы общих моделей */
    /*LEFT JOIN cms_tree as source_tree_parent ON source_tree_parent.id = ce_main_parent.tree_id*/
    /* Разделы моделей */
    LEFT JOIN cms_tree as source_tree ON source_tree.id = ce_main.tree_id
    /* Разделы товаров на новом сайте */
	LEFT JOIN (
	    SELECT * FROM cms_tree as inner_new_tree WHERE inner_new_tree.cms_site_id = @site_id
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
LIMIT {limit};


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









INSERT IGNORE
    INTO shop_product_price (`created_at`,`updated_at`,`product_id`, `type_price_id`, `price`, `currency_code`)
SELECT
    UNIX_TIMESTAMP(),
    UNIX_TIMESTAMP(),
    receiver_product_id,
    receiver_type_price_id,
    calc_price,
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
                shop_type_price as stp
            WHERE
                stp.cms_site_id = @site_id
                AND is_default = 1
        ) as receiver_type_price_id
    FROM
        shop_product_price as sprice
        /*берем все цены*/
        LEFT JOIN shop_product as sp on sp.id = sprice.product_id
        /*товары связанные с ценами*/
        LEFT JOIN cms_content_element as cce on cce.id = sp.id
        /*элементы контента к этим товарам*/
        INNER JOIN shop_product as main_sp on main_sp.id = sp.main_pid
        /*все это ищем только в главных товарах*/
        LEFT JOIN (

            /*задания на импорт для этого сайта*/
            SELECT
                shop_import_cms_site.*
            FROM
                shop_import_cms_site
            WHERE
                shop_import_cms_site.cms_site_id = @site_id
        ) as siteimport on siteimport.sender_cms_site_id = cce.cms_site_id
        /*привязываем задание на импорт к каждому элементу контента*/
        LEFT JOIN (
            SELECT
                inner_sp.*
            FROM
                shop_product as inner_sp
                LEFT JOIN cms_content_element as inner_cce on inner_cce.id = inner_sp.id
            WHERE
                inner_cce.cms_site_id = @site_id
        ) as sp_for_import on sp_for_import.main_pid = sp.main_pid
        /*Привязать товары сайта на который будет идти загрузка цен*/
    WHERE
        cce.cms_site_id IN (
            SELECT
                shop_import_cms_site.sender_cms_site_id
            FROM
                shop_import_cms_site
            WHERE
                shop_import_cms_site.cms_site_id = @site_id
        )
        and sprice.type_price_id IN (
            SELECT
                shop_import_cms_site.sender_shop_type_price_id
            FROM
                shop_import_cms_site
            WHERE
                shop_import_cms_site.cms_site_id = @site_id
        )
        and sp_for_import.id is not null
    GROUP BY
        main_sp.id
    ORDER BY
        siteimport.priority DESC

) as q;


COMMIT;








