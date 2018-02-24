SELECT
	RB_person.id as personID,
	RB_person.username,
	RB_person.firstname,
	RB_person.lastname,
	PLM_account.id as accountID,
	PLM_account.title as accountTitle,
	PLM_account.locked,
	PLM_account.activated,
	PLM_account.administrator,
	PLM_account.parent_id,
	PLM_account.company_id
FROM RB_person
INNER JOIN PLM_personToAccount ON RB_person.id = PLM_personToAccount.person_id
INNER JOIN PLM_account ON PLM_personToAccount.account_id = PLM_account.id
WHERE (RB_person.username = '$username' || RB_person.mail = '$username') AND PLM_account.password = '$password';