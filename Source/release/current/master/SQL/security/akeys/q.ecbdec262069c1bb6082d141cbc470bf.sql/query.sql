SELECT API_key.*, API_keyType.name AS type_name, API_keyType.user_group_id
FROM API_key
INNER JOIN API_keyType ON API_key.type_id = API_keyType.id
WHERE akey = '{akey}' OR ({include_p_akey} = 1 AND previous_akey = '{akey}');