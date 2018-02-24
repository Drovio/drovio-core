SELECT VCS_projectCommit.author_id
FROM VCS_projectCommit
WHERE project_id = {pid}
GROUP BY VCS_projectCommit.author_id;