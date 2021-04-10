UPDATE
    `shop_product` as update_sp
    INNER JOIN (
        SELECT
            cce.cms_site_id,

            cce_parent.cms_site_id as parent_cms_site_id,

            sp.id as secondary_product_id,
            cce_parent.id as secondary_product_pid, /*Такой общий товар долженыть быть*/
            sp.product_type as secondary_product_type, /*У текущих товаров такой тип*/

            main_sp.product_type as main_product_type, /*А должен быть как на портале такой*/
            main_sp.offers_pid as main_product_pid, /*На портале такой общий товар*/

            main_sp.id as main_id /*Идентификатор модели*/
        FROM
            shop_product sp
            LEFT JOIN cms_content_element cce on cce.id = sp.id
            LEFT JOIN shop_site shop_site on shop_site.id = cce.cms_site_id

            INNER JOIN shop_product main_sp on main_sp.id = cce.main_cce_id /*Подтягиваем главные товары портала*/
            LEFT JOIN shop_product main_sp_parent on main_sp_parent.id = main_sp.offers_pid /*Общие товары портала*/

            /*LEFT JOIN shop_product sp_parent on sp_parent.main_pid = main_sp_parent.id*/
            LEFT JOIN cms_content_element cce_parent on cce_parent.main_cce_id = main_sp.offers_pid AND cce_parent.cms_site_id = 3
        WHERE
            /*sp.product_type != main_sp.product_type*/ /*Только товары у которых не совпадает тип с порталом*/
            shop_site.is_receiver = 1 /*Касается только сайтов получаетелей*/
            AND cce.cms_site_id = 3
            /*AND (cce_parent.cms_site_id is null OR cce_parent.cms_site_id = 3)*/
    ) as inner_sp on inner_sp.secondary_product_id = update_sp.id
SET
    update_sp.`product_type` = inner_sp.main_product_type,
    update_sp.`offers_pid` = inner_sp.secondary_product_pid
;