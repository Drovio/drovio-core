SELECT API_keyType.*, PLM_userGroup.name as user_group_name
FROM API_keyType
LEFT OUTER JOIN PLM_userGroup ON API_keyType.user_group_id = PLM_userGroup.id;