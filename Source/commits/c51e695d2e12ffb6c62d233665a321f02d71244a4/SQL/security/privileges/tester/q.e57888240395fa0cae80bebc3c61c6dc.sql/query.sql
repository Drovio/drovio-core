SELECT node.id, node.description, (COUNT(parent.description) - 1) AS depth 
FROM UNIT_moduleGroup AS node, UNIT_moduleGroup AS parent 
WHERE node.lft BETWEEN parent.lft AND parent.rgt 
GROUP BY node.id 
HAVING node.id IN (
	SELECT moduleGroup_id
	FROM DVC_testWorkspace 
	WHERE DVC_testWorkspace.account_id = $aid
	UNION
	SELECT moduleGroup_id 
	FROM DVC_devWorkspace 
	WHERE DVC_devWorkspace.account_id = $aid) 
ORDER BY node.lft ASC