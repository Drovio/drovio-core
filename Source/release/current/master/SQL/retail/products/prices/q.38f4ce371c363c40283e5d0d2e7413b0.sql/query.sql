UPDATE RTL_product
SET tax_rate_id = {rate}
WHERE RTL_product.owner_company_id = {cid} AND RTL_product.id = '{pid}';