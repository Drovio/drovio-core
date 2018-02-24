SELECT UNIT_page.*
FROM UNIT_page 
INNER JOIN UNIT_pageFolder on UNIT_page.folder_id = UNIT_pageFolder.id 
INNER JOIN UNIT_domain on UNIT_pageFolder.domain = UNIT_domain.name 
WHERE UNIT_pageFolder.id = $id