SELECT RL_personAddress.*, RL_addressType.name AS type
FROM RL_personAddress
INNER JOIN RL_addressType ON RL_addressType.id = RL_personAddress.type_id
WHERE person_id = {pid};