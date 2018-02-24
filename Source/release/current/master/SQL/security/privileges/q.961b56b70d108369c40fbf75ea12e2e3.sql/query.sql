DELETE FROM MDL_modulePMGroup
WHERE module_id IN ({ids}) AND user_group_id = {gid};