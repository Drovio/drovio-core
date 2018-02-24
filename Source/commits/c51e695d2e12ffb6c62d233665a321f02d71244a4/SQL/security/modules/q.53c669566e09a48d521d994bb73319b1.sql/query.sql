-- Get moduleID and accountID
SELECT $moduleID INTO @module;
SELECT $accountID INTO @accountID;

-- Check if account is in some group of module
SELECT COUNT(PLM_userGroup.id) > 0 INTO @in_group
FROM PLM_userGroup
INNER JOIN PLM_userGroupCommand ON PLM_userGroupCommand.userGroup_id = PLM_userGroup.id 
INNER JOIN PLM_accountAtGroup ON PLM_accountAtGroup.userGroup_id = PLM_userGroup.id
WHERE PLM_accountAtGroup.account_id = @accountID AND PLM_userGroupCommand.module_id = @module;

-- Get Module information
SELECT UNIT_module.scope, UNIT_module.status INTO @scope, @status
FROM UNIT_module
WHERE UNIT_module.id = @module;

-- Set Conditions
-- Scope OPEN or PUBLIC  => USER
SELECT IF((@scope = 1) OR (@scope = 2), 'user', NULL) INTO @access; 

-- Scope PROTECTED && IN_GROUP => USER
SELECT IF((@scope = 3) AND @in_group AND @access IS NULL, 'user', @access) INTO @access;

-- Scope PROTECTED && NOT IN_GROUP => AUTH
SELECT IF((@scope = 3) AND NOT @in_group AND @access IS NULL, 'auth', @access) INTO @access;

-- Scope PRIVATE && IN_GROUP => ONAUTH
SELECT IF((@scope = 4) AND @in_group AND @access IS NULL, 'onauth', @access) INTO @access;

-- Scope PRIVATE && NOT IN_GROUP => NO
SELECT IF((@scope = 4) AND NOT @in_group AND @access IS NULL, 'no', @access) INTO @access;

-- Status NOT ON => UC | OFF
SELECT IF(@status != 1, IF(@status = 2, 'uc', 'off'), @access) INTO @access;

-- Select result
SELECT @module AS module, @status AS status, @scope AS scope, @access AS access;