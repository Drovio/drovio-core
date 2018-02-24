SELECT PLM_account.id as accountID, PLM_account.title, RB_person.firstname, RB_person.lastname
FROM PLM_account
LEFT OUTER JOIN PLM_personToAccount ON PLM_personToAccount.account_id = PLM_account.id
LEFT OUTER JOIN RB_person ON PLM_personToAccount.person_id = RB_person.id
INNER JOIN VCS_projectCommit ON VCS_projectCommit.author_id = PLM_account.id
WHERE project_id = {pid}
GROUP BY VCS_projectCommit.author_id;