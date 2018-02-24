-- Get moduleID
SELECT {moduleID} INTO @module;

-- Get Module information
SELECT MDL_module.scope, MDL_module.status INTO @scope, @status
FROM MDL_module
WHERE MDL_module.id = @module;

-- Set Conditions
-- Scope OPEN
SELECT IF((@scope = 1), 'open', "no") INTO @access;

-- Status NOT ON => UC | OFF
SELECT IF(@status != 1, IF(@status = 2, 'uc', 'off'), @access) INTO @access;

-- Select result
SELECT @module AS module, @status AS status, @scope AS scope, @access AS access;