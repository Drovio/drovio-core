-- Create the relation person
INSERT INTO RL_person (firstname, middle_name, lastname, owner_company_id)
VALUES ('{firstname}', '{middle_name}', '{lastname}', {tid});

-- Select person id
SELECT last_insert_id() as id;