-- Get User's Primary Account
SELECT PLM_userAccount.id INTO @account_id
FROM PLM_userAccount
INNER JOIN PLM_userToAccount ON PLM_userToAccount.account_id = PLM_userAccount.id
INNER JOIN PLM_user ON PLM_user.id = PLM_userToAccount.user_id
WHERE PLM_userAccount.administrator = 1 AND PLM_user.id = $user_id;

-- Get userGroup_id from userGroupName
SELECT PLM_userGroup.id INTO @group_id
FROM PLM_userGroup
WHERE PLM_userGroup.name = '$groupName' AND PLM_userGroup.company_id &lt;=&gt; $company_id;

-- Delete account from userGroup
DELETE FROM PLM_userAccountAtGroup
WHERE PLM_userAccountAtGroup.account_id = @account_id AND PLM_userAccountAtGroup.userGroup_id = @group_id AND PLM_userAccountAtGroup.company_id &lt;=&gt; $company_id;