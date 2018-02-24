SELECT
	RTL_productStock.branch_id,
	RTL_branch.title AS branchName,
	RTL_branch.address AS branchAddress,
	SUM(RTL_productStock.stock) AS stock
FROM RTL_productStock
INNER JOIN RTL_branch ON RTL_productStock.branch_id = RTL_branch.id
WHERE RTL_productStock.product_id = '{pid}'
GROUP BY RTL_productStock.branch_id;