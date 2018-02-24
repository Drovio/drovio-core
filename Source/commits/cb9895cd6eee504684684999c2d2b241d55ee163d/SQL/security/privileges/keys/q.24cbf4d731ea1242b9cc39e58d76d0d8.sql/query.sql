SELECT PLM_accountKey.*
FROM PLM_accountKey, PLM_accountAtGroup
WHERE PLM_accountKey.account_id = PLM_accountAtGroup.account_id AND PLM_accountAtGroup.userGroup_id = PLM_accountKey.userGroup_id
	AND PLM_accountKey.account_id = {aid} AND PLM_accountKey.type_id = {type} AND PLM_accountKey.context = {context}
	AND PLM_accountKey.userGroup_id IN (
		SELECT PLM_userGroupCommand.userGroup_id
		FROM PLM_userGroupCommand
		WHERE PLM_userGroupCommand.module_id = {mid}
	)