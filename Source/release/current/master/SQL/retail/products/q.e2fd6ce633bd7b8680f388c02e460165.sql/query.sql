SELECT RTL_product.*, RTL_taxRate.rate, RTL_taxRate.rate as tax_rate, RTL_productMUnit.title AS unit, RTL_productMUnit.title AS m_unit
FROM RTL_product
LEFT OUTER JOIN RTL_productMUnit ON RTL_productMUnit.id = RTL_product.m_unit_id
LEFT OUTER JOIN RTL_taxRate ON RTL_taxRate.id = RTL_product.tax_rate_id
WHERE RTL_product.owner_company_id = {cid} AND RTL_product.id = '{pid}';