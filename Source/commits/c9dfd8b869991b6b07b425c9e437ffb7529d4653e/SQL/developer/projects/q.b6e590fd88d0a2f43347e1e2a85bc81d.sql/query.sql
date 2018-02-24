SELECT DEV_project.*
FROM DEV_project
INNER JOIN DEV_accountToProject on DEV_accountToProject.project_id = DEV_project.id
WHERE DEV_accountToProject.account_id = {aid}