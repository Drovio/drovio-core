UPDATE PLM_accountSession
SET 
	ip = '$ip',
	lastAccess = '$date',
	userAgent = '$userAgent'
WHERE PLM_accountSession.id = $sid AND PLM_accountSession.accountID = $aid;