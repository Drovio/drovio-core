SELECT node.id, node.description, (COUNT(parent.description) - 1) AS depth, node.parent_id
FROM MDL_moduleGroup AS node, MDL_moduleGroup AS parent 
WHERE node.lft BETWEEN parent.lft AND parent.rgt 
GROUP BY node.id 
ORDER BY node.lft ASC