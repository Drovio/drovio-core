-- Get product prices, including tax rate
SELECT RTL_CproductPrice.*, RTL_productPriceType.title AS type, RTL_taxRate.rate
FROM RTL_CproductPrice
INNER JOIN RTL_Cproduct ON RTL_CproductPrice.product_id = RTL_Cproduct.id
INNER JOIN RTL_taxRate ON RTL_Cproduct.taxRate_id = RTL_taxRate.id
INNER JOIN RTL_productPriceType ON RTL_CproductPrice.type_id = RTL_productPriceType.id
WHERE RTL_Cproduct.company_id = {cid} AND RTL_Cproduct.product_id = {pid};