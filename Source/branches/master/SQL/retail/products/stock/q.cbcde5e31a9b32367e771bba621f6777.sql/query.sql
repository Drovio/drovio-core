-- Set stock
INSERT INTO RTL_productStock (product_id, branch_id, storage_id, stock)
VALUES ('{pid}', {bid}, {storage}, {stock})
ON DUPLICATE KEY UPDATE stock = {stock};