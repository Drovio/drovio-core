-- Insert new application
INSERT INTO RB_apps (name, fullName, scope, tags, description, authorID)
VALUES ('$name', '$fullName', '$scope', '$tags', '$description', $authorID);

-- Get application id
SELECT last_insert_id() AS id;