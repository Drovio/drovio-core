SELECT 
UNIT_module.id AS module_id, 
UNIT_module.group_id, 
UNIT_module.title AS module_title, 
UNIT_module.description AS module_description, 
UNIT_moduleScope.id as scope_id, 
UNIT_moduleScope.description as scope, 
UNIT_moduleStatus.id as status_id, 
UNIT_moduleStatus.description as status, 
UNIT_moduleGroup.description AS group_description 
FROM UNIT_module 
INNER JOIN UNIT_moduleGroup on UNIT_module.group_id = UNIT_moduleGroup.id 
INNER JOIN UNIT_moduleStatus on UNIT_module.status = UNIT_moduleStatus.id 
INNER JOIN UNIT_moduleScope on UNIT_module.scope = UNIT_moduleScope.id 
WHERE UNIT_module.id = $id