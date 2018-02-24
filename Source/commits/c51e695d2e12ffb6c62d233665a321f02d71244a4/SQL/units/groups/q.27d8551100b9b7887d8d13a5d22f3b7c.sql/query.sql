SELECT node.* 
FROM UNIT_moduleGroup AS node, UNIT_moduleGroup AS parent 
WHERE node.lft BETWEEN parent.lft AND parent.rgt AND parent.id = '$id' 
ORDER BY node.lft