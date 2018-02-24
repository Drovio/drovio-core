UPDATE LIB_teamProject
SET version = '{version}'
WHERE LIB_teamProject.team_id = {tid} AND LIB_teamProject.project_id = {pid};