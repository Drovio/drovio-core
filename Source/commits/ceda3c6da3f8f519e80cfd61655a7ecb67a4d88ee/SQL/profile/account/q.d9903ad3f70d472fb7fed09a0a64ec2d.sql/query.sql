SELECT
	PLM_account.id as accountID,
	PLM_account.title as accountTitle,
	PLM_account.*
FROM PLM_account
WHERE PLM_account.id = {id}