SELECT RTL_product.*
FROM RTL_product
INNER JOIN RTL_productCode ON RTL_productCode.product_id = RTL_product.id
WHERE RTL_productCode.type_id = '{type}' AND RTL_productCode.code = '{code}' AND RTL_productCode.expiration_time > {time};