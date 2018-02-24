INSERT INTO RTL_productInfoValue (product_id, info_id, value)
VALUES ({pid}, {iid}, '{value}')
ON DUPLICATE KEY UPDATE value = '{value}';