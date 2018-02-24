SELECT
	MDL_module.*,
	MDL_moduleScope.description AS scope_desc,
	MDL_moduleStatus.description AS status_desc
FROM MDL_module
INNER JOIN MDL_moduleScope ON MDL_module.scope = MDL_moduleScope.id
INNER JOIN MDL_moduleStatus ON MDL_module.status = MDL_moduleStatus.id;