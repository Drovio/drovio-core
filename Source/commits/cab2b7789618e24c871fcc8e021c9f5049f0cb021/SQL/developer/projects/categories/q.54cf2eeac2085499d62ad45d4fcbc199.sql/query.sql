-- Create new projectType
INSERT INTO DEV_projectType (name, projectCategory)
VALUES ('{name}', {category});

-- Get projectType id
SELECT last_insert_id() AS id;