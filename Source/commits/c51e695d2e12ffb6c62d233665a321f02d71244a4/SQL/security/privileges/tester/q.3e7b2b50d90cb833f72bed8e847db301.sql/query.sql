SELECT *
FROM UNIT_module 
WHERE group_id IN (
	SELECT moduleGroup_id
	FROM DVC_testWorkspace 
	WHERE DVC_testWorkspace.account_id = $aid)