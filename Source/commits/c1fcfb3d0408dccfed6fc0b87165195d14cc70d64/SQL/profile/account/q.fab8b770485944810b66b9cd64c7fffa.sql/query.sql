SELECT PLM_account.id, PLM_account.title
FROM PLM_account
INNER JOIN PLM_personToAccount ON PLM_account.id = PLM_personToAccount.account_id
INNER JOIN RB_person ON RB_person.id = PLM_personToAccount.person_id
WHERE PLM_account.administrator = 1 AND RB_person.mail = '{mail}';