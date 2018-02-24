SELECT *
FROM BT_issue
WHERE project_id = {pid} AND version = '{version}'
ORDER BY time_created;