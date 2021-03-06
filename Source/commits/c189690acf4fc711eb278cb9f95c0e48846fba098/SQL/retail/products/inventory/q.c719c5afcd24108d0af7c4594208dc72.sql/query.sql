SELECT node.id, node.title, node.description, node.parent_id, (COUNT(parent.id) - 1) AS depth
FROM RTL_productHierarchy AS node, RTL_productHierarchy AS parent
WHERE node.lft BETWEEN parent.lft AND parent.rgt AND (node.company_id = {cid} || node.company_id IS NULL)
GROUP BY node.id 
ORDER BY node.lft ASC