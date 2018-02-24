SELECT parent.* 
FROM UNIT_moduleGroup AS node, UNIT_moduleGroup AS parent 
WHERE node.lft BETWEEN parent.lft AND parent.rgt AND node.id = {id}
ORDER BY parent.lft;