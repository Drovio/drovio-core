DELETE FROM PLM_accountSession
WHERE PLM_accountSession.id = $sid AND PLM_accountSession.accountID = $aid;