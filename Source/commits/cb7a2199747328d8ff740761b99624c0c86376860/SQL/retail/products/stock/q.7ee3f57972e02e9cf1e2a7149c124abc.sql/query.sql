SELECT
	RTL_productQuantity.branch_id, 
	RTL_branch.title AS branchName, 
	RTL_branch.address AS branchAddress, 
	RTL_productQuantity.storage_id, 
	RTL_storageUnit.title AS storageTitle, 
	RTL_productQuantity.quantity
FROM RTL_productQuantity
INNER JOIN RTL_branch ON RTL_productQuantity.branch_id = RTL_branch.id
INNER JOIN RTL_storageUnit ON RTL_productQuantity.storage_id = RTL_storageUnit.id
WHERE RTL_productQuantity.product_id = '{pid}';