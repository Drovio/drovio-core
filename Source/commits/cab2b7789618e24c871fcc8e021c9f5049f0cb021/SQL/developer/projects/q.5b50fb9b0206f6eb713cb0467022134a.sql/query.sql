-- Create new project
INSERT INTO DEV_project (title, projectCategory, repository, description)
VALUES ('{title}', {category}, '', '{description}');

-- Get project id
SELECT last_insert_id() AS id;