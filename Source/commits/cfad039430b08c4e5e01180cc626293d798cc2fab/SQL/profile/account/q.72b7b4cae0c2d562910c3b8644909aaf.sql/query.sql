-- Insert Basic Person Information
UPDATE RB_person
SET
	firstname = '{firstname}',
	lastname = '{lastname}',
	activated = 1
WHERE id = {pid};

-- Create an account for this person
INSERT INTO PLM_account (title, description, administrator, password) 
VALUES ('{accountTitle}', '', 1, '{password}');
SELECT LAST_INSERT_ID() INTO @accountID;

-- Create the connection between the person and the account
INSERT INTO PLM_personToAccount(person_id, account_id) 
VALUES ({pid}, @accountID);