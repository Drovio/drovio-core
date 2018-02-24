-- Get userGroup_id from userGroupName
SELECT PLM_userGroup INTO @group_id
FROM PLM_userGroup
WHERE PLM_userGroup.name = '$groupName' AND PLM_userGroup.company_id = $company;

-- Delete account from userGroup
DELETE FROM PLM_userAccountAtGroup
WHERE PLM_userAccountAtGroup.account_id = $account_id AND PLM_userAccountAtGroup.userGroup_id = @group_id AND PLM_userAccountAtGroup.company_id = $company_id;