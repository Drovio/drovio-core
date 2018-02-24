-- Create new project
INSERT INTO DEV_project (title, projectCategory, repository, description)
VALUES ('{title}', {category}, '{repository}', '{description}');

-- Get project id
SELECT last_insert_id() AS id;