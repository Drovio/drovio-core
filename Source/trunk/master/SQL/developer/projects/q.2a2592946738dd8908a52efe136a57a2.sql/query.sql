SELECT DEV_accountToProject.*, DEV_accountToProject.account_id as accountID
FROM DEV_accountToProject
WHERE DEV_accountToProject.project_id = {pid}
GROUP BY accountID;