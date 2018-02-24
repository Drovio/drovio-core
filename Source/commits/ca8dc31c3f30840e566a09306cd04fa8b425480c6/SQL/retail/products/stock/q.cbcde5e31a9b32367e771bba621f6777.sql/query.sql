-- Get generic product id
SELECT RTL_Cproduct.id INTO @productID
FROM RTL_Cproduct
WHERE RTL_Cproduct.company_id = {cid} AND RTL_Cproduct.product_id = {pid};

-- Set stock
INSERT INTO RTL_CproductQuantity (product_id, branch_id, storage_id, quantity)
VALUES (@productID, {bid}, {storage}, {quantity})
ON DUPLICATE KEY UPDATE quantity = {quantity};