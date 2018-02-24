-- Get next phone id
SELECT IFNULL(MAX(phone_id), 0) + 1 INTO @personPhoneID
FROM RL_personPhone
WHERE person_id = '{pid}';

-- Set phone id
SELECT CONCAT('{pid}', '_', @personPhoneID) INTO @phoneID;

-- Create new phone
INSERT INTO RL_personPhone (id, person_id, phone_id, type_id, phone, country_id)
VALUES (@phoneID, '{pid}', @personPhoneID, {type}, '{phone}', {cid});

-- Select phone id
SELECT @phoneID as id;