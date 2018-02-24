-- Insert page
INSERT INTO PG_page(folder_id, file) VALUES('{fid}', '{name}');

/* Get page id */
SELECT LAST_INSERT_ID() AS id;