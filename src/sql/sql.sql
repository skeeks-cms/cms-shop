/*Удаление неправильно привязанных цен*/
DELETE spp_for_delete
FROM shop_product_price spp_for_delete
INNER JOIN (
    SELECT
        spp.id
    FROM
        `shop_product_price` AS spp
    LEFT JOIN cms_content_element AS cce ON cce.id = spp.product_id
    LEFT JOIN shop_type_price AS pt ON pt.id = spp.type_price_id
    LEFT JOIN cms_site AS site ON site.id = pt.cms_site_id
    WHERE
        cce.cms_site_id != pt.cms_site_id
) as spp_inner ON spp_inner.id = spp_for_delete.id

