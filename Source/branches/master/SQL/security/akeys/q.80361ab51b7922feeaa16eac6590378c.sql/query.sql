SELECT *
FROM API_key
WHERE (akey = '{akey}' OR (previous_akey = '{akey}' AND {etime} < time_expires)) AND type_id = {type} AND (account_id = {aid} OR account_id IS NULL) AND (team_id = {tid} OR team_id IS NULL) AND (project_id = {pid} OR project_id IS NULL);