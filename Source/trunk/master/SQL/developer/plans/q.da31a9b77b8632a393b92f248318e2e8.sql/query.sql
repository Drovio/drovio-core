SELECT *
FROM DEV_teamPlan
WHERE team_id = {tid}
ORDER BY time_created DESC
LIMIT 1