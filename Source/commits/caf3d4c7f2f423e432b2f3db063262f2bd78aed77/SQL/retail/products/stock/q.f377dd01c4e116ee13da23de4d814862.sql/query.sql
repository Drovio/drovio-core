UPDATE RTL_product
SET m_unit_id = {unit}
WHERE RTL_product.owner_company_id = {cid} AND RTL_product.product_id = '{pid}';