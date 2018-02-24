-- Get moduleID and accountID
SELECT {module_id} INTO @module;
SELECT {account_id} INTO @accountID;

-- Check if account is in some group of module
SELECT COUNT(*) > 0 INTO @in_group
FROM PLM_userGroupCommand
WHERE PLM_userGroupCommand.module_id = @module AND PLM_userGroupCommand.userGroup_id IN ({group_ids});

-- Get Module information
SELECT UNIT_module.scope, UNIT_module.status INTO @scope, @status
FROM UNIT_module
WHERE UNIT_module.id = @module;

-- Set Conditions
-- Scope OPEN => OPEN-Module
SELECT IF(@scope = 1, 'open', NULL) INTO @access;

-- Scope PUBLIC => USER
SELECT IF(@scope = 2, 'user', @access) INTO @access;

-- Scope PROTECTED && IN_GROUP => USER
SELECT IF((@scope = 3) AND @in_group, 'user', @access) INTO @access;

-- Scope PROTECTED && NOT IN_GROUP => NO
SELECT IF((@scope = 3) AND NOT @in_group, 'no', @access) INTO @access;

-- Status NOT ON => UC | OFF
SELECT IF(@status != 1, IF(@status = 2, 'uc', 'off'), @access) INTO @access;

-- Select result
SELECT @module AS module, @status AS status, @scope AS scope, @access AS access;