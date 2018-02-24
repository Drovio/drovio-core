INSERT INTO eBLD_siteTemplate (type, templateGroup, date_modified, status, owner) 
VALUES ('$type', '$templateGroup', NOW(), '$status', '$userId');
SELECT LAST_INSERT_ID() AS last_id;