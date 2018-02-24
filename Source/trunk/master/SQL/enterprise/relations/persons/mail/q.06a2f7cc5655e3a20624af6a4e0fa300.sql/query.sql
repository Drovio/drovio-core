-- Get next mail id
SELECT IFNULL(MAX(mail_id), 0) + 1 INTO @personMailID
FROM RL_personMail
WHERE person_id = '{pid}';

-- Set mail id
SELECT CONCAT('{pid}', '_', @personMailID) INTO @mailID;

-- Create new mail
INSERT INTO RL_personMail (id, person_id, mail_id, type_id, mail)
VALUES (@mailID, '{pid}', @personMailID, {type}, '{mail}');

-- Select mail id
SELECT @mailID as id;