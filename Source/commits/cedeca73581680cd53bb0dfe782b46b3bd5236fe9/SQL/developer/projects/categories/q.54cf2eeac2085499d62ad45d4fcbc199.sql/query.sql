-- Create new projectType
INSERT INTO DEV_projectCategory (name, projectType)
VALUES ('{name}', {type});

-- Get projectType id
SELECT last_insert_id() AS id;