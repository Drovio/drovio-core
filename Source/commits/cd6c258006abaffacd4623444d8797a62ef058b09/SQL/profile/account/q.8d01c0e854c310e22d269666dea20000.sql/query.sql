-- Get next id for given account
SELECT IFNULL(MAX(id), 0) INTO @sessionID
FROM PLM_accountSession
WHERE accountID = {aid};

-- Create new Account Session
INSERT INTO PLM_accountSession(id, accountID, salt, ip, lastAccess, userAgent, rememberme)
VALUES (@sessionID + 1, {aid}, '{salt}', '{ip}', '{date}', '{userAgent}', {rememberme});

-- Select session id
SELECT @sessionID + 1 as id;