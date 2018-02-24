-- Create new project
INSERT INTO DEV_project (title, description, projectType, projectStatus)
VALUES ('{title}', '{description}', {type}, 1);

-- Get project id
SELECT last_insert_id() AS id;