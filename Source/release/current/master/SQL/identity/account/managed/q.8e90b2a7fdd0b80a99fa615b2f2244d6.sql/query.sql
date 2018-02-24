-- Create account
INSERT INTO ID_account (title, description, username, password, locked, parent_id)
VALUES ('{title}', '{description}', '{username}', '{password}', {locked}, {parent_id});

-- Get account id created
SELECT last_insert_id() INTO @accountID;

-- Create person connection
INSERT INTO ID_personToAccount (person_id, account_id)
VALUES ({pid}, @accountID);

-- Select account id
SELECT @accountID as id;