SELECT *
FROM VCS_projectRelease
WHERE project_id = {pid} AND branch_name = '{branch}' AND version = '{version}'
ORDER BY time DESC;