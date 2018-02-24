-- Set stock
INSERT INTO RTL_productQuantity (product_id, branch_id, storage_id, quantity)
VALUES ('{pid}', {bid}, {storage}, {quantity})
ON DUPLICATE KEY UPDATE quantity = {quantity};