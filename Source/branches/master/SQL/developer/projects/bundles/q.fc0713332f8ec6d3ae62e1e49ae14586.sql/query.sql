SELECT DEV_project.*
FROM BSS_bundle_project
INNER JOIN DEV_project ON BSS_bundle_project.project_id = DEV_project.id
WHERE BSS_bundle_project.bundle_id = {id}