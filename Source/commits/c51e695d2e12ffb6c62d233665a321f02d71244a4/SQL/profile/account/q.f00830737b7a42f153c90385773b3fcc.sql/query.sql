SELECT PLM_userGroup.name,COUNT(PLM_accountAtGroup.account_id) FROM PLM_userGroup
INNER JOIN PLM_accountAtGroup ON PLM_accountAtGroup.userGroup_id=PLM_userGroup.id
GROUP BY PLM_userGroup.id