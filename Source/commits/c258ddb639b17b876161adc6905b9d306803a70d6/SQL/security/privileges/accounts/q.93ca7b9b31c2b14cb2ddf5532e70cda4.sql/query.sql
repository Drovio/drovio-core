-- Insert account to userGroup
INSERT IGNORE INTO PLM_accountAtGroup (account_id, userGroup_id)
VALUES ({aid}, {gid});