-- Get generic product id
SELECT RTL_Cproduct.id INTO @productID
FROM RTL_Cproduct
WHERE RTL_Cproduct.company_id = {cid} AND RTL_Cproduct.product_id = {pid};

-- Get stock
SELECT RTL_CproductQuantity.branch_id, RTL_Cbranch.title AS branchName, RTL_Cbranch.address AS branchAddress,Â RTL_CproductQuantity.storage_id, RTL_CstorageUnit.title AS storageTitle, RTL_CproductQuantity.quantity
FROM RTL_CproductQuantity
INNER JOIN RTL_Cbranch ON RTL_CproductQuantity.branch_id = RTL_Cbranch.id
INNER JOIN RTL_CstorageUnit ON RTL_CproductQuantity.storage_id = RTL_CstorageUnit.id
WHERE RTL_CproductQuantity.product_id = @productID;