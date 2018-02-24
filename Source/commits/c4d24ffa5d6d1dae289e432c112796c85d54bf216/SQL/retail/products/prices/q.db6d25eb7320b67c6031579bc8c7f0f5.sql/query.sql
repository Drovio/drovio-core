-- Get product id
SELECT id INTO @productID
FROM RTL_Cproduct
WHERE RTL_Cproduct.company_id = {cid} AND RTL_Cproduct.product_id = {pid};

-- Remove product price
DELETE FROM RTL_CproductPrice
WHERE product_id = @productID AND type_id = {type};