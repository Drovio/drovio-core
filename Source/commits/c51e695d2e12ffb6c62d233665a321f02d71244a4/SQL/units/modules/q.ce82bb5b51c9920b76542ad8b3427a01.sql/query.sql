-- Insert new module
INSERT INTO UNIT_module (title, description, group_id, scope, status) 
VALUES ('$title', '$description', $group_id, 3, 1);

-- Get the module id
SELECT LAST_INSERT_ID() AS last_id;