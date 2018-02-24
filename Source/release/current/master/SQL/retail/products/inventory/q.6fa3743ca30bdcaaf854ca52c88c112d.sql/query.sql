SELECT node.id, node.title, node.description, node.parent_id
FROM RTL_productHierarchy AS node, RTL_productHierarchy AS parent 
WHERE node.lft BETWEEN parent.lft AND parent.rgt AND node.id IN (
	SELECT DISTINCT(RTL_Cproduct.hierarchy_id)
	FROM RTL_Cproduct
	WHERE RTL_Cproduct.company_id = parent.company_id AND RTL_Cproduct.company_id = {cid}
)
GROUP BY node.id 
ORDER BY node.lft ASC