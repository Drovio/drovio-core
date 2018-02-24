SELECT DEV_projectRelease.*, RB_team.name as teamName, DEV_project.description, DEV_projectType.name AS type, PLM_account.title AS reviewAccountTitle
FROM DEV_projectRelease
INNER JOIN DEV_project ON DEV_project.id = DEV_projectRelease.project_id
INNER JOIN RB_team ON DEV_project.team_id = RB_team.id
LEFT OUTER JOIN PLM_account ON PLM_account.id = DEV_projectRelease.review_account_id
INNER JOIN DEV_projectType ON DEV_project.projectType = DEV_projectType.id
WHERE DEV_projectRelease.project_id = {pid}
ORDER BY DEV_projectRelease.time_created DESC