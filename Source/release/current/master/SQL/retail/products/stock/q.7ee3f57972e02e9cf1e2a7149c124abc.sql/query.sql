SELECT
	RTL_productStock.branch_id, 
	RTL_branch.title AS branchName, 
	RTL_branch.address AS branchAddress, 
	RTL_productStock.storage_id, 
	RTL_storageUnit.title AS storageTitle, 
	RTL_productStock.stock
FROM RTL_productStock
INNER JOIN RTL_branch ON RTL_productStock.branch_id = RTL_branch.id
INNER JOIN RTL_storageUnit ON RTL_productStock.storage_id = RTL_storageUnit.id
WHERE RTL_productStock.product_id = '{pid}';