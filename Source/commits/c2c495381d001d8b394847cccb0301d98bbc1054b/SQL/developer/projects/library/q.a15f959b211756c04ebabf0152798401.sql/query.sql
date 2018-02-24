UPDATE LIB_teamProject
SET next_version = '{version}'
WHERE LIB_teamProject.team_id = {tid} AND LIB_teamProject.project_id = {pid};