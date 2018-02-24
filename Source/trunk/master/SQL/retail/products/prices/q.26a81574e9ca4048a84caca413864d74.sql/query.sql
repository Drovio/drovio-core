-- Get product prices, including tax rate
SELECT RTL_productPrice.*, RTL_productPriceType.title AS type, RTL_taxRate.rate
FROM RTL_productPrice
INNER JOIN RTL_product ON RTL_product.id = RTL_productPrice.product_id
LEFT OUTER JOIN RTL_taxRate ON RTL_product.tax_rate_id = RTL_taxRate.id
INNER JOIN RTL_productPriceType ON RTL_productPrice.type_id = RTL_productPriceType.id
WHERE RTL_productPrice.product_id = '{pid}';