SELECT node.* 
FROM MDL_moduleGroup AS node, MDL_moduleGroup AS parent 
WHERE node.lft BETWEEN parent.lft AND parent.rgt AND parent.id = {id}
ORDER BY node.lft