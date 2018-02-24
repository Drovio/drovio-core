SELECT PLM_account.*
FROM PLM_account
INNER JOIN PLM_personToAccount ON PLM_personToAccount.account_id = PLM_account.id
WHERE PLM_personToAccount.person_id = {pid};