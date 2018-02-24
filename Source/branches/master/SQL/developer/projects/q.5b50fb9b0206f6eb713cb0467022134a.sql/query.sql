-- Create new project
INSERT INTO DEV_project (title, description, projectType, team_id)
VALUES ('{title}', '{description}', {type}, {tid});

-- Get project id
SELECT last_insert_id() AS id;