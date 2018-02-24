SELECT DEV_projectRelease.*
FROM DEV_projectRelease
WHERE DEV_projectRelease.project_id = {pid} AND status_id = 2
ORDER BY time_created DESC
LIMIT 1;