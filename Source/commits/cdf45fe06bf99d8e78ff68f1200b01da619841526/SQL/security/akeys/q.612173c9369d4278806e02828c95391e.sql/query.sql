SELECT *
FROM API_key
WHERE (akey = '{akey}' OR (previous_akey = '{akey}' AND time_expires < {etime})) AND project_id = {pid};