SELECT PLM_account.*, PLM_account.id as accountID, RB_person.mail
FROM DEV_accountToProject
INNER JOIN PLM_account ON DEV_accountToProject.account_id = PLM_account.id
LEFT OUTER JOIN PLM_personToAccount ON PLM_personToAccount.account_id = PLM_account.id
LEFT OUTER JOIN RB_person ON PLM_personToAccount.person_id = RB_person.id
WHERE DEV_accountToProject.project_id = {pid}
GROUP BY accountID;