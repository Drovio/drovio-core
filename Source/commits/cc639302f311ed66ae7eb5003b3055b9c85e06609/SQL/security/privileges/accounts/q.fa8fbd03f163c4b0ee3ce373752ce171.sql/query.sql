-- Get userGroup_id from userGroupName
SELECT PLM_userGroup.id INTO @group_id
FROM PLM_userGroup
WHERE PLM_userGroup.name = '{groupName}';

-- Insert account to userGroup
INSERT IGNORE INTO PLM_accountAtGroup (account_id, userGroup_id)
VALUES ({account_id}, @group_id);