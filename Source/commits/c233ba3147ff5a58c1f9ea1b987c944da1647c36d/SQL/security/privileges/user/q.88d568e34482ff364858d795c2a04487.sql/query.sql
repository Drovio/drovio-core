SELECT PLM_userGroup.*
FROM PLM_userGroup
INNER JOIN PLM_userGroupCommand ON PLM_userGroupCommand.userGroup_id = PLM_userGroup.id
WHERE PLM_userGroupCommand.module_id = {mid}