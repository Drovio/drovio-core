-- Get next version (if any)
SELECT LIB_teamProject.next_version INTO @next_version
FROM LIB_teamProject
WHERE LIB_teamProject.team_id = {tid} AND LIB_teamProject.project_id = {pid};

-- Update if not null
UPDATE LIB_teamProject
SET version = IFNULL(@next_version, LIB_teamProject.version), next_version = NULL
WHERE LIB_teamProject.team_id = {tid} AND LIB_teamProject.project_id = {pid};

-- Select new version
SELECT LIB_teamProject.version AS version
FROM LIB_teamProject
WHERE LIB_teamProject.team_id = {tid} AND LIB_teamProject.project_id = {pid};