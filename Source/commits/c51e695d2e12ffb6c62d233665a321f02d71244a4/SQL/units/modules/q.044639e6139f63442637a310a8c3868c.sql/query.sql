SELECT 
UNIT_module.id, 
UNIT_module.title, 
UNIT_module.description AS module_description, 
UNIT_module.scope AS scope_id, 
UNIT_moduleScope.description AS scope, 
UNIT_module.status AS status_id,
UNIT_moduleStatus.description AS status, 
UNIT_module.group_id, 
UNIT_moduleGroup.description AS group_description
FROM UNIT_module
INNER JOIN UNIT_moduleScope ON UNIT_moduleScope.id = UNIT_module.scope
INNER JOIN UNIT_moduleStatus ON UNIT_moduleStatus.id = UNIT_module.status
INNER JOIN UNIT_moduleGroup ON UNIT_moduleGroup.id = UNIT_module.group_id
WHERE UNIT_module.scope > 2 && UNIT_module.status < 3
ORDER BY UNIT_module.id ASC