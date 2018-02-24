-- Create new mail
INSERT INTO RL_personMail (person_id, type_id, mail)
VALUES ({pid}, {type}, '{mail}');

-- Select mail id
SELECT last_insert_id() as id;