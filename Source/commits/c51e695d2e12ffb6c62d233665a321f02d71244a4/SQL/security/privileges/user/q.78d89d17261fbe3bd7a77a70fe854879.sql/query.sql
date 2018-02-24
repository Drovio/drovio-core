-- Get userGroup_id from userGroupName
SELECT PLM_userGroup INTO @group_id
FROM PLM_userGroup
WHERE PLM_userGroup.name = '$groupName' AND PLM_userGroup.company_id = $company;

-- Insert account to userGroup
INSERT IGNORE INTO PLM_userAccountAtGroup (account_id, userGroup_id, company_id)
VALUES ($account_id, @group_id, $company_id);