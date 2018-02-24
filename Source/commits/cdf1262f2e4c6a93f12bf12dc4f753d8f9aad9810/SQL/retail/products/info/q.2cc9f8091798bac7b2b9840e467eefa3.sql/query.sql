INSERT INTO RTL_productInfoValue (global_product_id, company_product_id, info_id, value)
VALUES ({gpid}, {cpid}, {info_id}, '{value}')
ON DUPLICATE KEY UPDATE value = '{value}';