SELECT *
FROM MDL_module 
WHERE group_id IN (
	SELECT moduleGroup_id
	FROM DVC_testWorkspace 
	WHERE DVC_testWorkspace.account_id = {aid})
	
UNION

SELECT *
FROM MDL_module 
WHERE group_id IN (
		SELECT moduleGroup_id
		FROM DVC_devWorkspace 
		WHERE DVC_devWorkspace.account_id = {aid})