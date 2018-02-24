-- Get User's Primary Account
SELECT PLM_userAccount.id INTO @account_id
FROM PLM_userAccount
INNER JOIN PLM_userToAccount ON PLM_userToAccount.account_id = PLM_userAccount.id
INNER JOIN PLM_user ON PLM_user.id = PLM_userToAccount.user_id
WHERE PLM_userAccount.administrator = 1 AND PLM_user.id = $user_id;

-- Get All groups of which this Account is a member
SELECT PLM_userGroup.*
FROM PLM_userGroup
INNER JOIN PLM_userAccountAtGroup ON PLM_userAccountAtGroup.userGroup_id = PLM_userGroup.id
WHERE PLM_userAccountAtGroup.account_id = @account_id;