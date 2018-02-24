UPDATE RL_person
SET firstname = '{firstname}', middle_name = '{middle_name}', lastname = '{lastname}', notes = '{notes}'
WHERE id = {pid} AND owner_company_id = {tid};