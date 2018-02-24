UPDATE DVC_devWorkspace
SET master = 0 
WHERE account_id = $aid AND moduleGroup_id = $gid