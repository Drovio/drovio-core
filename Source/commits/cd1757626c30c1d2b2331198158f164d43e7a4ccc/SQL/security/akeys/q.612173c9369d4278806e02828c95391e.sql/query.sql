SELECT *
FROM API_key
WHERE (akey = '{akey}' OR (previous_akey = '{akey}' AND {etime} < time_expires)) AND project_id = {pid};