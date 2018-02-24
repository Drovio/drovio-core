SELECT node.id, node.description, (COUNT(parent.id) - 1) AS depth 
FROM RTL_productHierarchy AS node, RTL_productHierarchy AS parent 
WHERE node.lft BETWEEN parent.lft AND parent.rgt AND parent.company_id = {cid}
GROUP BY node.id 
ORDER BY node.lft ASC