INSERT INTO eBLD_website (title, description, siteType_id, status_id, project_id, dateUpdated)
VALUES ('$wsName', '$websiteDescription', '$siteType_id', '$status_id', '$project_id', NOW());
SELECT LAST_INSERT_ID() AS last_id;