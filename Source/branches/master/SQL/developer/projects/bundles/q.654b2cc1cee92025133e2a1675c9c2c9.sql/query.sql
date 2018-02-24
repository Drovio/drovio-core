-- Create Bundle
INSERT INTO BSS_bundle (title, team_id)
VALUES ('{title}', {tid});

-- Get bundle id
SELECT last_insert_id() AS id;