UPDATE RL_personPhone
SET type_id = {type}, phone = '{phone}', country_id = {cid}
WHERE id = '{id}' AND person_id = '{pid}';