-- Get product id
SELECT id INTO @productID
FROM RTL_Cproduct
WHERE RTL_Cproduct.company_id = {cid} AND RTL_Cproduct.product_id = {pid};

-- Set product info value
INSERT INTO RTL_CproductInfoValue (product_id, info_id, value)
VALUES (@productID, {iid}, '{value}')
ON DUPLICATE KEY UPDATE value = '{value}';