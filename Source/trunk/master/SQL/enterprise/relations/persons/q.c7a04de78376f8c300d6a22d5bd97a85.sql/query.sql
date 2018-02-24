UPDATE RL_person
SET firstname = '{firstname}', middle_name = '{middle_name}', lastname = '{lastname}', notes = '{notes}', date_of_birth = '{birthday}'
WHERE id = '{pid}' AND owner_team_id = {tid};