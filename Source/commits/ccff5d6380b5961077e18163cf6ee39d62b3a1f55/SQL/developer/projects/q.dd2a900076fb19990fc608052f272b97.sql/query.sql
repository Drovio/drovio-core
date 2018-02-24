SELECT *
FROM DEV_projectRelease
WHERE DEV_projectRelease.project_id = {pid}
ORDER BY DEV_projectRelease.time_created DESC