SELECT
	VCS_projectWorkingItemAuthor.*,
	VCS_projectWorkingItem.item_id AS id,
	VCS_projectWorkingItem.force_commit AS force_commit,
	VCS_projectWorkingItem.last_update AS last_update,
	VCS_projectWorkingItem.last_author_id AS last_author_id,
	PLM_account.title AS last_author
FROM VCS_projectWorkingItemAuthor
INNER JOIN VCS_projectWorkingItem ON VCS_projectWorkingItemAuthor.item_id = VCS_projectWorkingItem.item_id
INNER JOIN PLM_account ON VCS_projectWorkingItem.last_author_id = PLM_account.id
WHERE VCS_projectWorkingItemAuthor.project_id = {pid} AND VCS_projectWorkingItem.branch_name = '{branch}' AND VCS_projectWorkingItemAuthor.author_id = {author};