SELECT last_commit_id
FROM VCS_projectBranch
WHERE project_id = {pid} AND branch_name = '{branch}';