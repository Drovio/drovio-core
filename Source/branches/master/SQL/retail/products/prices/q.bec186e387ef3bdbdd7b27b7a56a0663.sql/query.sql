-- Set product price
INSERT INTO RTL_productPrice (product_id, type_id, price, time_updated)
VALUES ('{pid}', {type}, {price}, {time})
ON DUPLICATE KEY UPDATE price = {price}, time_updated = {time};