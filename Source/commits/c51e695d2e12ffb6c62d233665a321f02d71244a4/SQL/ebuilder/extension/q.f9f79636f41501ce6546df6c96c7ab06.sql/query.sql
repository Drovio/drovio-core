INSERT INTO eBLD_extension (category, date_modified, status, owner) 
VALUES ('$category', NOW(), '$status', '$userId');
SELECT LAST_INSERT_ID() AS last_id;