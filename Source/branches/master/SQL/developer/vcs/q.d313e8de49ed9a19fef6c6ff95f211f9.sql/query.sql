SELECT VCS_projectCommitItem.*
FROM VCS_projectCommitItem
INNER JOIN VCS_projectCommit ON VCS_projectCommit.id = VCS_projectCommitItem.commit_id
WHERE VCS_projectCommit.project_id = {pid} AND VCS_projectCommit.id = '{cid}';