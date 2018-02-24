-- Remove default value from all account teams
UPDATE PLM_accountToTeam
SET def = 0
WHERE account_id = {aid};

-- Set default team
UPDATE PLM_accountToTeam
SET def = 1
WHERE team_id = {tid} AND account_id = {aid};