SELECT DEV_projectRelease.*
FROM DEV_projectRelease
WHERE DEV_projectRelease.project_id = {pid} AND DEV_projectRelease.version = '{version}'