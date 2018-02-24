SELECT parent.* 
FROM UNIT_pageFolder AS node, UNIT_pageFolder AS parent 
WHERE node.lft BETWEEN parent.lft AND parent.rgt AND node.id = '$id' 
ORDER BY parent.lft