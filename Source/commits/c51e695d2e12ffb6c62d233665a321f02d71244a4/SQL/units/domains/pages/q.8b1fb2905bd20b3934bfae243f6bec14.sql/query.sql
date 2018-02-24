SELECT 
UNIT_page.id, 
UNIT_page.file, 
UNIT_page.module_id AS module_id,
UNIT_module.group_id AS moduleGroup_id,
UNIT_page.static, 
UNIT_page.sitemap, 
UNIT_pageFolder.id AS folder_id, 
UNIT_pageFolder.name AS folder_name, 
UNIT_pageFolder.domain AS domain_description, 
UNIT_domain.path AS domain_path
FROM UNIT_page 
INNER JOIN UNIT_pageFolder on UNIT_page.folder_id = UNIT_pageFolder.id 
INNER JOIN UNIT_domain on UNIT_pageFolder.domain = UNIT_domain.name 
LEFT OUTER JOIN UNIT_module on UNIT_page.module_id = UNIT_module.id 
WHERE UNIT_page.id = $id