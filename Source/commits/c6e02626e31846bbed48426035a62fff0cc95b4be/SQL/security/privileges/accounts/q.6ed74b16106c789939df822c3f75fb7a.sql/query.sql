SELECT
	RB_person.id,
	RB_person.username,
	RB_person.firstname,
	RB_person.lastname,
	PLM_account.id as accountID,
	PLM_account.title as accountTitle,
	PLM_account.locked,
	PLM_account.activated,
	PLM_account.parent_id,
	PLM_account.company_id
FROM RB_person
INNER JOIN PLM_personToAccount ON RB_person.id = PLM_personToAccount.person_id
INNER JOIN PLM_account ON PLM_personToAccount.account_id = PLM_account.id
INNER JOIN PLM_accountAtGroup ON PLM_accountAtGroup.account_id = PLM_account.id
INNER JOIN PLM_userGroup ON PLM_userGroup.id = PLM_accountAtGroup.userGroup_id
WHERE PLM_userGroup.name = '{userGroup}' AND administrator = 1;