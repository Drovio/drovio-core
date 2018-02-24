SELECT *
FROM UNIT_module
INNER JOIN PLM_userGroupCommand ON PLM_userGroupCommand.module_id = UNIT_module.id
WHERE PLM_userGroupCommand.userGroup_id = $gid