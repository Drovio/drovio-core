SELECT VCS_projectCommit.*, PLM_account.title AS author
FROM VCS_projectCommit
INNER JOIN PLM_account ON VCS_projectCommit.author_id = PLM_account.id
WHERE project_id = {pid} AND branch_name = '{branch}'
ORDER BY time DESC;