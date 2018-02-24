UPDATE VCS_projectBranch
SET last_commit_id = '{commit}'
WHERE project_id = {pid} AND branch_name = '{branch}';