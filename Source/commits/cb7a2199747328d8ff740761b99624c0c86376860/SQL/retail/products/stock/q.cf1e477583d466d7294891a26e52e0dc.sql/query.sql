SELECT
	RTL_productQuantity.branch_id,
	RTL_branch.title AS branchName,
	RTL_branch.address AS branchAddress,
	SUM(RTL_productQuantity.quantity) AS quantity
FROM RTL_productQuantity
INNER JOIN RTL_branch ON RTL_productQuantity.branch_id = RTL_branch.id
WHERE RTL_productQuantity.product_id = '{pid}'
GROUP BY RTL_productQuantity.branch_id;