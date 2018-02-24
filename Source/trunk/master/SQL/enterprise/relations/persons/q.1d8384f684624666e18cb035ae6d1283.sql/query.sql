UPDATE RL_person
SET identity_account_id = {aid}
WHERE id = '{pid}' AND owner_team_id = {tid};