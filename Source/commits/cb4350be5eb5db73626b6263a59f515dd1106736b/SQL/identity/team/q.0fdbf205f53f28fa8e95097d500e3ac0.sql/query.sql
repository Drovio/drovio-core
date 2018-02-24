-- Remove default value from all account teams
UPDATE ID_teamAccount
SET def = 0
WHERE account_id = {aid};

-- Set default team
UPDATE ID_teamAccount
SET def = 1
WHERE team_id = {tid} AND account_id = {aid};