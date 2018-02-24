DELETE FROM DEV_projectRelease
WHERE project_id = {pid} AND version = '{version}' AND status_id = 1;