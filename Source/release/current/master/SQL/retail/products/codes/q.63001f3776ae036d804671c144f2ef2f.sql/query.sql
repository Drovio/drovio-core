INSERT INTO RTL_productCode (product_id, type_id, code, expiration_time)
VALUES ('{id}', {type}, '{code}', {expire})
ON DUPLICATE KEY UPDATE code = '{code}', expiration_time = {expire};