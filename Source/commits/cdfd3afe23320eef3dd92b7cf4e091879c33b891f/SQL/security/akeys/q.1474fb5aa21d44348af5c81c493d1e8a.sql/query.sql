SELECT API_key.*, API_keyType.name AS type_name, API_keyType.user_group_id
FROM API_key
INNER JOIN API_keyType ON API_key.type_id = API_keyType.id
WHERE project_id = {pid} AND account_id = {aid};