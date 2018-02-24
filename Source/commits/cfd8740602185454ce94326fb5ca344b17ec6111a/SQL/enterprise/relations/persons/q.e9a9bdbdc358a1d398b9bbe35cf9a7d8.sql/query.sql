UPDATE RL_person
SET drovio_person_id = {dpid}
WHERE person_id = {pid} AND owner_company_id = {tid};