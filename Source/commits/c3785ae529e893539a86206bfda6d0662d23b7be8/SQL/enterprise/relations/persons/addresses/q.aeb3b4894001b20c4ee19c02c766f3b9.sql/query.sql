-- Get next address id
SELECT IFNULL(MAX(address_id), 0) + 1 INTO @personAddressID
FROM RL_personAddress
WHERE person_id = '{pid}';

-- Set address id
SELECT CONCAT('{pid}', '_', @personAddressID) INTO @addressID;

-- Create new address
INSERT INTO RL_personAddress (id, person_id, address_id, type_id, address, postal_code, city, country_id)
VALUES (@addressID, '{pid}', @personAddressID, {type}, '{address}', '{pcode}', '{city}', {cid});

-- Select address id
SELECT @addressID as id;