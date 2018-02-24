SELECT RTL_productCode.*, RTL_productCodeType.title AS type, RTL_productCodeType.description AS type_description
FROM RTL_productCode
INNER JOIN RTL_productCodeType ON RTL_productCode.type_id = RTL_productCodeType.id
WHERE RTL_productCode.product_id = {pid};