INSERT INTO RL_personInfo (person_id, info)
VALUES ('{pid}', '{info}')
ON DUPLICATE KEY UPDATE info = '{info}';