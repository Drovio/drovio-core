SELECT 
	PG_page.id,
	PG_page.file,
	PG_page.module_id AS module_id,
	PG_page.attributes,
	PG_page.static,
	PG_page.sitemap,
	PG_pageFolder.id AS folder_id,
	PG_pageFolder.name AS folder_name,
	PG_pageFolder.domain AS domain_description,
	PG_domain.path AS domain_path,
	UNIT_module.group_id AS moduleGroup_id
FROM PG_page 
INNER JOIN PG_pageFolder on PG_page.folder_id = PG_pageFolder.id 
INNER JOIN PG_domain on PG_pageFolder.domain = PG_domain.name 
LEFT OUTER JOIN UNIT_module on PG_page.module_id = UNIT_module.id 
WHERE PG_page.id = {id}