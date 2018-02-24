-- Get userGroup_id from groupName
SELECT PLM_userGroup.id INTO @group_id
FROM PLM_userGroup
WHERE PLM_userGroup.name = '{groupName}';

-- Delete account from userGroup
DELETE FROM PLM_accountAtGroup
WHERE PLM_accountAtGroup.account_id = {account_id} AND PLM_accountAtGroup.userGroup_id = @group_id;