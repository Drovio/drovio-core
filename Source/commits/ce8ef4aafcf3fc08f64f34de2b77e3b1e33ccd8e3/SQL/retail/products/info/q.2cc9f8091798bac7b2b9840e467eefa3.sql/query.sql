INSERT INTO RTL_productInfoValue (global_product_id, company_product_id, info_id, value)
VALUES ({gpid}, {cpid}, {iid}, '{value}')
ON DUPLICATE KEY UPDATE value = '{value}';