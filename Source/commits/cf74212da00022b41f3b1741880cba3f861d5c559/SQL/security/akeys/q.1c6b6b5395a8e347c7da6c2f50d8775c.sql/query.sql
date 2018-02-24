SELECT API_key.*, API_keyType.user_group_id, PLM_userGroup.name AS user_group_name
FROM API_key
INNER JOIN API_keyType ON API_key.type_id = API_keyType.id
LEFT OUTER JOIN PLM_userGroup ON API_keyType.user_group_id = PLM_userGroup.id
WHERE account_id = {aid};