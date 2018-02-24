SELECT parent.* 
FROM MDL_moduleGroup AS node, MDL_moduleGroup AS parent 
WHERE node.lft BETWEEN parent.lft AND parent.rgt AND node.id = {id}
ORDER BY parent.lft;