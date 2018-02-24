SELECT UNIT_module.*, UNIT_moduleScope.description AS scope_desc, UNIT_moduleStatus.description AS status_desc
FROM UNIT_module
INNER JOIN UNIT_moduleScope ON UNIT_module.scope = UNIT_moduleScope.id
INNER JOIN UNIT_moduleStatus ON UNIT_module.status = UNIT_moduleStatus.id
WHERE UNIT_module.group_id = {gid};