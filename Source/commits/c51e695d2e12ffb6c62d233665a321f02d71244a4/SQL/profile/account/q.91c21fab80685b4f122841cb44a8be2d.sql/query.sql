SELECT
	PLM_account.id as account_id,
	RB_person.username,
	RB_person.mail
FROM RB_person
INNER JOIN PLM_personToAccount ON PLM_personToAccount.person_id = RB_person.id
INNER JOIN PLM_account ON PLM_personToAccount.account_id = PLM_account.id
WHERE PLM_account.administrator = 1;