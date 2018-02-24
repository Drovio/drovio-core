-- Set item as deleted
UPDATE VCS_projectItem
SET deleted = 1
WHERE id = '{id}' AND project_id = {pid};

-- Remove item from working index 
DELETE FROM VCS_projectWorkingItem
WHERE project_id = {pid} AND branch_name = '{branch}' AND item_id = '{id}';

-- Remove item from authors index 
DELETE FROM VCS_projectWorkingItemAuthor
WHERE project_id = {pid} AND item_id = '{id}';