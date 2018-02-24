-- Create new address
INSERT INTO RL_personAddress (person_id, type_id, address, postal_code, city, country_id)
VALUES ({pid}, {type}, '{address}', '{pcode}', '{city}', {cid});

-- Select address id
SELECT last_insert_id() as id;