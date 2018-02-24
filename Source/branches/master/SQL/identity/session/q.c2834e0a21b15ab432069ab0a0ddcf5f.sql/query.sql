SELECT *
FROM ID_accountSession
WHERE ID_accountSession.account_id = {aid}
ORDER BY ID_accountSession.lastAccess DESC;