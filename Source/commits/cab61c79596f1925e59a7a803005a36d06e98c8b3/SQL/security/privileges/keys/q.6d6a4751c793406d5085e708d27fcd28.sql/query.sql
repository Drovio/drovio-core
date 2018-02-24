SELECT PLM_accountKey.*, PLM_accountKeyType.type, PLM_userGroup.name AS groupName
FROM PLM_accountKey
INNER JOIN PLM_accountKeyType ON PLM_accountKey.type_id = PLM_accountKeyType.id 
WHERE PLM_accountKey.account_id = {aid} AND PLM_accountKey.userGroup_id = '{gid}' AND PLM_accountKey.type_id = {type} AND PLM_accountKey.context = {context}
ORDER BY PLM_accountKey.time_created DESC