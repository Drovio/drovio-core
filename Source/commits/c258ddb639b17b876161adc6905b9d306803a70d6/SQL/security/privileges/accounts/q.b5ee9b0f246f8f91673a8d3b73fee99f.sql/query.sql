-- Delete account from userGroup
DELETE FROM PLM_accountAtGroup
WHERE PLM_accountAtGroup.account_id = {aid} AND PLM_accountAtGroup.userGroup_id = {gid};