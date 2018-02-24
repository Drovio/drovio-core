SELECT node.id, node.name, node.domain, node.is_root, (COUNT(parent.name) - 1) AS depth 
FROM UNIT_pageFolder AS node, UNIT_pageFolder AS parent 
WHERE node.lft BETWEEN parent.lft AND parent.rgt
GROUP BY node.id 
HAVING node.domain = '{domain}'
ORDER BY node.lft ASC