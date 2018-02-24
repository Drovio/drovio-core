-- Create new projectStatus
INSERT INTO DEV_projectStatus (name)
VALUES ('{name}');

-- Get projectStatus id
SELECT last_insert_id() AS id;