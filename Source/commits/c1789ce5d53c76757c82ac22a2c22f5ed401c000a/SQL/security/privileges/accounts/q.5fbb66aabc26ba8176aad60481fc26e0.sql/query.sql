SELECT 
	PLM_account.id as accountID,
	PLM_account.title,
	PLM_account.locked,
	PLM_account.activated,
	PLM_account.parent_id,
	PLM_account.company_id
FROM PLM_account
INNER JOIN PLM_accountAtGroup ON PLM_accountAtGroup.account_id = PLM_account.id
INNER JOIN PLM_userGroup ON PLM_userGroup.id = PLM_accountAtGroup.userGroup_id
WHERE PLM_userGroup.name = '{userGroup}'
AND PLM_account.administrator != 1;