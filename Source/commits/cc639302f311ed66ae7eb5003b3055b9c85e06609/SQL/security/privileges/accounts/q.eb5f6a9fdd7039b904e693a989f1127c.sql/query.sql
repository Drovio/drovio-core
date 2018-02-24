-- Delete account from userGroup
DELETE FROM PLM_accountAtGroup
WHERE PLM_accountAtGroup.account_id = {account_id} AND PLM_accountAtGroup.userGroup_id = {group_id};