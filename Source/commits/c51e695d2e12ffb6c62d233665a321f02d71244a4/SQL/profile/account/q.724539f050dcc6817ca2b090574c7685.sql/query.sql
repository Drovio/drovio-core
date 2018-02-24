-- Update account
UPDATE PLM_account
SET reset = IF('$reset' = "", NULL, '$reset')
WHERE id = $accountID;