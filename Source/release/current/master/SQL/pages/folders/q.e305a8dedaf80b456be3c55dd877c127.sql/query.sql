SELECT parent.* 
FROM PG_pageFolder AS node, PG_pageFolder AS parent 
WHERE node.lft BETWEEN parent.lft AND parent.rgt AND node.id = {id}
ORDER BY parent.lft