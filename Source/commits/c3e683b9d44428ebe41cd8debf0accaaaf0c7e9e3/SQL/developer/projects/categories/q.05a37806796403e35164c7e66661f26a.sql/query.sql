-- Create new projectCategory
INSERT INTO DEV_projectCategory (name)
VALUES ('{name}');

-- Get projectCategory id
SELECT last_insert_id() AS id;