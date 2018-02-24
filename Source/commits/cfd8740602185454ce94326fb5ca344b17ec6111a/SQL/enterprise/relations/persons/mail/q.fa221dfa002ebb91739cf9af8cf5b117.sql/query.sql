SELECT RL_personMail.*, RL_mailType.name AS type
FROM RL_personMail
INNER JOIN RL_mailType ON RL_mailType.id = RL_personMail.type_id
WHERE person_id = {pid};