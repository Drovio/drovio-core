DELETE FROM PLM_userGroupCommand
WHERE module_id IN ($ids) AND userGroup_id = $gid