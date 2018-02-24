SELECT PLM_userGroup.*
FROM PLM_userGroup
INNER JOIN PLM_accountAtGroup ON PLM_accountAtGroup.userGroup_id = PLM_userGroup.id
WHERE PLM_accountAtGroup.account_id = {id};