-- Insert Basic Person Information
INSERT INTO RB_person (firstname, lastname, mail, activated) 
VALUES ('{firstname}', '{lastname}', '{email}', 1);
SELECT LAST_INSERT_ID() INTO @personID;

-- Create an account for this person
INSERT INTO PLM_account (title, description, administrator, password) 
VALUES ('{accountTitle}', '', 1, '{password}');
SELECT LAST_INSERT_ID() INTO @accountID;

-- Create the connection between the person and the account
INSERT INTO PLM_personToAccount(person_id, account_id) 
VALUES (@personID, @accountID);