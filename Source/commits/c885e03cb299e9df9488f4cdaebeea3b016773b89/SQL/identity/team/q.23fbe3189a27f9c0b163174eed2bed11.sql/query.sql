-- Create new team
INSERT INTO ID_team (name, uname, description, owner_account_id)
VALUES ('{name}', '{uname}', '{description}', {aid});

-- Get team id
SELECT last_insert_id() INTO @teamID;

-- Insert account as member of the team
INSERT INTO ID_teamAccount(team_id, account_id)
VALUES (@teamID, {aid});