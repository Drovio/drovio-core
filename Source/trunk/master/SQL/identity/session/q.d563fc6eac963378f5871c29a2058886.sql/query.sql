-- Get next id for given account
SELECT IFNULL(MAX(id), 0) + 1 INTO @sessionID
FROM ID_accountSession
WHERE account_id = '{aid}';

-- Create new Account Session
INSERT INTO ID_accountSession(id, account_id, salt, ip, lastAccess, userAgent, rememberme)
VALUES (@sessionID , {aid}, '{salt}', '{ip}', '{date}', '{userAgent}', {rememberme});

-- Select session id
SELECT @sessionID as id;