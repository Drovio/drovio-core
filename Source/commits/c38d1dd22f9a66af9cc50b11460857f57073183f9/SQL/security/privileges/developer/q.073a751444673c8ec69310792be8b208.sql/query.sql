SELECT *
FROM UNIT_module 
WHERE group_id IN (
	SELECT moduleGroup_id
	FROM DVC_devWorkspace 
	WHERE DVC_devWorkspace.account_id = {aid})