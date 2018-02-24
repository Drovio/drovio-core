SELECT branch_name as head_branch
FROM VCS_projectBranch
WHERE project_id = {pid} AND head = 1;