UPDATE RTL_product
SET tax_rate_id = {rate}
WHERE RTL_product.company_id = {cid} AND RTL_Cproduct.product_id = '{pid}';