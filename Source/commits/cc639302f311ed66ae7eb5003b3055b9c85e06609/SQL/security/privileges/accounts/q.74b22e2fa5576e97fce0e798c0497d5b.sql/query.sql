-- Insert account to userGroup
INSERT IGNORE INTO PLM_accountAtGroup (account_id, userGroup_id)
VALUES ({account_id}, {group_id});