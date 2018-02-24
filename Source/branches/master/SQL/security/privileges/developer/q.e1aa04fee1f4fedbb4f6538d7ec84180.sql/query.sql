SELECT child.id, child.description, (COUNT(parent.id) - 1) AS depth 
FROM MDL_moduleGroup AS child, MDL_moduleGroup AS parent 
WHERE child.lft BETWEEN parent.lft AND parent.rgt 
GROUP BY child.id 
HAVING child.id IN (
	SELECT moduleGroup_id
	FROM DVC_devWorkspace 
	WHERE DVC_devWorkspace.account_id = {aid}
) 
ORDER BY child.lft ASC