SELECT *
FROM API_key
WHERE type_id = {type} AND (account_id = {aid} OR account_id IS NULL) AND (team_id = {tid} OR team_id IS NULL) AND (project_id = {pid} OR project_id IS NULL);