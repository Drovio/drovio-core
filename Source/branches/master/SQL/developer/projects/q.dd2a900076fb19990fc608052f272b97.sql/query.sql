SELECT DEV_projectRelease.*, DEV_project.description, DEV_projectType.name AS type
FROM DEV_projectRelease
INNER JOIN DEV_project ON DEV_project.id = DEV_projectRelease.project_id
INNER JOIN DEV_projectType ON DEV_project.projectType = DEV_projectType.id
WHERE DEV_projectRelease.project_id = {pid}
ORDER BY DEV_projectRelease.time_created DESC