SELECT VCS_projectCommit.*, PLM_account.title AS author
FROM VCS_projectCommit
INNER JOIN PLM_account ON VCS_projectCommit.author_id = PLM_account.id
INNER JOIN VCS_projectCommitItem ON VCS_projectCommit.id = VCS_projectCommitItem.commit_id
WHERE project_id = {pid} AND branch_name = '{branch}' AND VCS_projectCommitItem.item_id = '{item}';