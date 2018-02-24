SELECT 
	MDL_module.id AS module_id, 
	MDL_module.group_id, 
	MDL_module.title AS module_title, 
	MDL_module.description AS module_description, 
	MDL_moduleScope.id as scope_id, 
	MDL_moduleScope.description as scope, 
	MDL_moduleStatus.id as status_id, 
	MDL_moduleStatus.description as status, 
	MDL_moduleGroup.description AS group_description 
FROM MDL_module 
INNER JOIN MDL_moduleGroup on MDL_module.group_id = MDL_moduleGroup.id 
INNER JOIN MDL_moduleStatus on MDL_module.status = MDL_moduleStatus.id 
INNER JOIN MDL_moduleScope on MDL_module.scope = MDL_moduleScope.id;