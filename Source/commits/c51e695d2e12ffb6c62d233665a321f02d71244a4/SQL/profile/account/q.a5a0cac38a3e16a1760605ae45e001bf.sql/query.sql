SELECT *
FROM PLM_accountSession
WHERE PLM_accountSession.accountID = $aid
ORDER BY PLM_accountSession.lastAccess DESC;