-- Insert Basic Person Information
INSERT INTO RB_person (firstname, lastname, mail) 
VALUES ('{firstname}', '{lastname}', '$email');
SELECT LAST_INSERT_ID() INTO @personID;

-- Create an account for this person
INSERT INTO PLM_account (title, description, administrator, password) 
VALUES ('{firstname} {lastname}', 'Personal Administrator Account', 1, '$password');
SELECT LAST_INSERT_ID() INTO @accountID;

-- Create the connection between the person and the account
INSERT INTO PLM_personToAccount(person_id, account_id) 
VALUES (@personID, @accountID);