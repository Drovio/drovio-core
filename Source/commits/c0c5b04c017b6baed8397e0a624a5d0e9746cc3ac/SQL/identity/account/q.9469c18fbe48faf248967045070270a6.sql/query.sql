-- Create Person Entry
INSERT INTO ID_person (firstname, lastname, mail) 
VALUES ('{firstname}', '{lastname}', '{email}');
SELECT last_insert_id() INTO @personID;

-- Create an account for this person
INSERT INTO ID_account (title, description, administrator, password) 
VALUES ('{title}', '', 1, '{password}');
SELECT last_insert_id() INTO @accountID;

-- Create the connection between the person and the account
INSERT INTO ID_personToAccount(person_id, account_id) 
VALUES (@personID, @accountID);

-- Select account id
SELECT @accountID as id;