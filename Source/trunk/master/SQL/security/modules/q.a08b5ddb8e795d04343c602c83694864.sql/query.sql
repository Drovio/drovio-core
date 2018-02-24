-- Get moduleID and accountID
SELECT {module_id} INTO @module;
SELECT {account_id} INTO @accountID;

-- Check if account is in some group of module
SELECT COUNT(*) > 0 INTO @in_group
FROM MDL_modulePMGroup
WHERE MDL_modulePMGroup.module_id = @module AND MDL_modulePMGroup.user_group_id IN ({group_ids});

-- Get Module information
SELECT MDL_module.scope, MDL_module.status INTO @scope, @status
FROM MDL_module
WHERE MDL_module.id = @module;

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