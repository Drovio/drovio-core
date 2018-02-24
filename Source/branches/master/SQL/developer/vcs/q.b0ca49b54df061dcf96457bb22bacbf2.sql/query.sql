-- Remove head indicator from all project branches
UPDATE VCS_projectBranch
SET head = 0
WHERE project_id = {pid};

-- Set head branch for the given branch 
UPDATE VCS_projectBranch
SET head = 1
WHERE project_id = {pid} AND branch_name = '{branch}';