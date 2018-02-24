SELECT *
FROM UNIT_module 
WHERE id = $mid AND group_id IN (
	SELECT moduleGroup_id
	FROM DVC_devWorkspace 
	WHERE DVC_devWorkspace.account_id = $aid)