INSERT INTO DEV_teamPlan (team_id, plan_id, time_created, type)
VALUES ({tid}, {pid}, {time}, {type})
ON DUPLICATE KEY UPDATE time_created = {time}, type = {type};