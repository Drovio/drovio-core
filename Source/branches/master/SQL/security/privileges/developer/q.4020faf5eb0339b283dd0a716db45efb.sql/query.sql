UPDATE DVC_devWorkspace
SET master = 1 
WHERE account_id = $aid AND moduleGroup_id = $gid