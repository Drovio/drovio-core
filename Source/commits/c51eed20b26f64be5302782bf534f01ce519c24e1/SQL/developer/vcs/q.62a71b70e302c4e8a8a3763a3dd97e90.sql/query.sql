UPDATE VCS_projectItem
SET deleted = 1
WHERE id = '{id}' AND project_id = {pid};