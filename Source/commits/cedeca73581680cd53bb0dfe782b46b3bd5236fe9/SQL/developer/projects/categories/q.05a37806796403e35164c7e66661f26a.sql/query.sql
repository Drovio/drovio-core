-- Create new projectType
INSERT INTO DEV_projectType (name)
VALUES ('{name}');

-- Get projectType id
SELECT last_insert_id() AS id;