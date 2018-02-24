-- Create unverified/unactivated person
INSERT INTO RB_person (firstname, lastname, mail, activated) 
VALUES ('{firstname}', '{lastname}', '{email}', 0);
SELECT LAST_INSERT_ID() AS id;