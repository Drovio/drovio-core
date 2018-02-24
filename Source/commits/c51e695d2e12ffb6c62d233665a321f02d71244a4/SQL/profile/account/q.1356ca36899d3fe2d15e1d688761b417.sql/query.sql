-- Get account ID
SELECT PLM_personToAccount.account_id INTO @accountID
FROM PLM_personToAccount
INNER JOIN PLM_account ON PLM_account.id = PLM_personToAccount.account_id
INNER JOIN RB_person ON RB_person.id = PLM_personToAccount.person_id
WHERE RB_person.mail = '$mail' AND PLM_account.administrator = 1;

-- Update account
UPDATE PLM_account
SET reset = '$reset'
WHERE id = @accountID;