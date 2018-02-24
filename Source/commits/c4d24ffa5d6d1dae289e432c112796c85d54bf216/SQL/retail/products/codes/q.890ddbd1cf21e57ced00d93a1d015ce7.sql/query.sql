UPDATE RTL_productCode
SET code = '{code}', expiration_time = {expire}
WHERE RTL_productCode.product_id = {id} AND RTL_productCode.type_id = {type};