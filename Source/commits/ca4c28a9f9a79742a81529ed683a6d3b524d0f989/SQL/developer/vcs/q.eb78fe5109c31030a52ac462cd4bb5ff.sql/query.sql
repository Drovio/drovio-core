SELECT *
FROM VCS_projectReleasePackage
WHERE project_id = {pid} AND branch_name = '{branch}'
ORDER BY version DESC;