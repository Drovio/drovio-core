-- Create new phone
INSERT INTO RL_personPhone (person_id, type_id, phone, country_id)
VALUES ({pid}, {type}, '{phone}', {cid});

-- Select phone id
SELECT last_insert_id() as id;