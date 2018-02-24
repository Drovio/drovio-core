SELECT
	VCS_projectWorkingItemAuthor.*,
	VCS_projectWorkingItem.item_id AS id,
	VCS_projectWorkingItem.force_commit AS force_commit,
	VCS_projectWorkingItem.last_update AS last_update,
	VCS_projectWorkingItem.last_author_id AS last_author_id
FROM VCS_projectWorkingItemAuthor
INNER JOIN VCS_projectWorkingItem ON VCS_projectWorkingItemAuthor.item_id = VCS_projectWorkingItem.item_id
WHERE VCS_projectWorkingItem.project_id = VCS_projectWorkingItemAuthor.project_id AND VCS_projectWorkingItem.project_id = {pid} AND VCS_projectWorkingItem.branch_name = '{branch}'
ORDER BY VCS_projectWorkingItem.last_update DESC;