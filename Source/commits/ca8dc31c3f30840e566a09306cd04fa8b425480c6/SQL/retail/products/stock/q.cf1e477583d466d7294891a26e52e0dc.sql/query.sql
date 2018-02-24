-- Get generic product id
SELECT RTL_Cproduct.id INTO @productID
FROM RTL_Cproduct
WHERE RTL_Cproduct.company_id = {cid} AND RTL_Cproduct.product_id = {pid};

-- Get stock
SELECT RTL_CproductQuantity.branch_id, RTL_Cbranch.title AS branchName, RTL_Cbranch.address AS branchAddress, SUM(RTL_CproductQuantity.quantity) AS quantity
FROM RTL_CproductQuantity
INNER JOIN RTL_Cbranch ON RTL_CproductQuantity.branch_id = RTL_Cbranch.id
WHERE RTL_CproductQuantity.product_id = @productID
GROUP BY RTL_CproductQuantity.branch_id;