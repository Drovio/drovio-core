SELECT VCS_projectCommit.*
FROM VCS_projectCommit
WHERE project_id = {pid} AND branch_name = '{branch}'
ORDER BY VCS_projectCommit.time DESC;