SELECT RL_personPhone.*, RL_phoneType.name AS type
FROM RL_personPhone
INNER JOIN RL_phoneType ON RL_phoneType.id = RL_personPhone.type_id
WHERE id = {id} AND person_id = {pid};