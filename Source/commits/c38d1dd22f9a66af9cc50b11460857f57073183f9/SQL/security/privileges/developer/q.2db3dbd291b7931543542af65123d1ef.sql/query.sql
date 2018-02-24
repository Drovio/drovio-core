SELECT moduleGroup_id As id
FROM DVC_devWorkspace 
WHERE DVC_devWorkspace.account_id = {aid} AND DVC_devWorkspace.master = 1