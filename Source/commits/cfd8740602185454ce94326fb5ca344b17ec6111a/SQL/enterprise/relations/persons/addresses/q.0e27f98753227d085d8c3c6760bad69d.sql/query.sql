UPDATE RL_personAddress
SET type_id = {type}, address = '{address}', postal_code = '{pcode}', city = '{city}', country_id = {cid}
WHERE id = {id} AND person_id = {pid};