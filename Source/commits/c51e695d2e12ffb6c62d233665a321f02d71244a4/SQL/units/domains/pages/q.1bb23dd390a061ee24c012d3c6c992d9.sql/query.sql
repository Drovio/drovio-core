INSERT INTO UNIT_page(folder_id, file) VALUES('$fid', '$name');
SELECT LAST_INSERT_ID() AS id;