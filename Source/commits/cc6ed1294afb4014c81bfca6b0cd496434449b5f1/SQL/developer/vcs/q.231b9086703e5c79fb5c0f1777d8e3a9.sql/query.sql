-- Remove item from generic working index
DELETE FROM VCS_projectWorkingItem
WHERE project_id = {pid} AND branch_name = '{branch}' AND item_id = '{item}';

-- Remove item from authors index
DELETE FROM VCS_projectWorkingItemAuthor
WHERE project_id = {pid} AND item_id = '{item}';