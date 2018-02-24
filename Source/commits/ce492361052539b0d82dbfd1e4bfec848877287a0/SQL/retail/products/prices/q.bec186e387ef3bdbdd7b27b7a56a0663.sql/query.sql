-- Get product id
SELECT id INTO @productID
FROM RTL_Cproduct
WHERE RTL_Cproduct.company_id = {cid} AND RTL_Cproduct.product_id = {pid};

-- Set product price
INSERT INTO RTL_CproductPrice (product_id, type_id, price, time_updated)
VALUES (@productID, {type}, {price}, {time})
ON DUPLICATE KEY UPDATE price = {price}, time = {time};