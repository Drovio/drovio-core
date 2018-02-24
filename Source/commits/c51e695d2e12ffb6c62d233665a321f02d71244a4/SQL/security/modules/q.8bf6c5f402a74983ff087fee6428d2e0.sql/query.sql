-- Get moduleID
SELECT $moduleID INTO @module;

-- Get Module information
SELECT UNIT_module.scope, UNIT_module.status INTO @scope, @status
FROM UNIT_module
WHERE UNIT_module.id = @module;

-- Set Conditions
-- Scope OPEN
SELECT IF((@scope = 1), 'user', "no") INTO @access;

-- Status NOT ON => UC | OFF
SELECT IF(@status != 1, IF(@status = 2, 'uc', 'off'), @access) INTO @access;

-- Select result
SELECT @module AS module, @status AS status, @scope AS scope, @access AS access;