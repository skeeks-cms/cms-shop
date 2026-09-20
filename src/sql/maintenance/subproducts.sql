UPDATE {{%shop_product}} sp
INNER JOIN {{%cms_content_element}} cce ON cce.id = sp.id
LEFT JOIN {{%shop_product}} main ON main.id = cce.main_cce_id
SET sp.measure_ratio = main.measure_ratio,
    sp.measure_ratio_min = main.measure_ratio_min,
    sp.measure_matches_jsondata = main.measure_matches_jsondata,
    sp.measure_code = main.measure_code,
    sp.width = main.width, sp.length = main.length,
    sp.height = main.height, sp.weight = main.weight
WHERE cce.main_cce_id IS NOT NULL AND sp.id BETWEEN :from_id AND :to_id