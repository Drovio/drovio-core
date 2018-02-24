UPDATE ID_accountSession
SET 
	ip = '{ip}',
	lastAccess = '{date}',
	userAgent = '{userAgent}',
	rememberme = {rememberme}
WHERE ID_accountSession.id = {sid} AND ID_accountSession.account_id = {aid};