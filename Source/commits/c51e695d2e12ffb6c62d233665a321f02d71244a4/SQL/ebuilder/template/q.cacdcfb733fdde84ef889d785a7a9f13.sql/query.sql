INSERT INTO eBLD_siteTemplateGroup (parent) 
VALUES ('$parent');
SELECT LAST_INSERT_ID() AS last_id;