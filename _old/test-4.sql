/*Удаление неправильно сохраненных цен*/

SELECT
	* 
FROM 
	`shop_product_price` as price 
	INNER JOIN shop_product as sp on sp.id = price.product_id 
	INNER JOIN cms_content_element as cce ON cce.id = sp.id 
    INNER JOIN shop_type_price as type_price ON type_price.id = price.type_price_id
WHERE cce.cms_site_id = 3 AND type_price.cms_site_id != cce.cms_site_id


DELETE inner_price
    FROM
        shop_product_price as inner_price
        INNER JOIN (
            SELECT
                price.*
            FROM
                `shop_product_price` as price
                INNER JOIN shop_product as sp on sp.id = price.product_id
                INNER JOIN cms_content_element as cce ON cce.id = sp.id
                INNER JOIN shop_type_price as type_price ON type_price.id = price.type_price_id
            WHERE cce.cms_site_id = 2 AND type_price.cms_site_id != cce.cms_site_id
        ) as join_price ON join_price.id = inner_price.id





/*Коллекции которые неправильно привязаны*/
select

	cce.id as product_id,
    cce.name as product_name,
    (
		SELECT
			property_brand.value_element_id
		FROM
			cms_content_element_property as property_brand
		WHERE
			property_brand.property_id = 28
			AND property_brand.element_id = cce.id
	) as product_brand_id,

    cce_collection.name as collection_name,
    (
		SELECT
			property_brand.value_element_id
		FROM
			cms_content_element_property as property_brand
		WHERE
			property_brand.property_id = 28
			AND property_brand.element_id = cce_collection.id
	) as collection_brand_id

FROM
	cms_content_element as cce
	INNER JOIN cms_content_element_property as property ON property.element_id = cce.id
	AND property.property_id = 63
	INNER JOIN cms_content_element as cce_collection ON cce_collection.id = property.value_element_id
WHERE
	cce.content_id = 2
	AND cce.cms_site_id = 1
    HAVING product_brand_id != collection_brand_id