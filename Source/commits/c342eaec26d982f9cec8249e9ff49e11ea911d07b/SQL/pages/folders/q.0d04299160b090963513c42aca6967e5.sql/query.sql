SELECT node.id, node.name, node.domain, node.parent_id
FROM PG_pageFolder AS node, PG_pageFolder AS parent
WHERE node.lft BETWEEN parent.lft AND parent.rgt
GROUP BY node.id
HAVING node.parent_id = {pid}
ORDER BY node.name ASC