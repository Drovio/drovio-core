SELECT PG_page.*
FROM PG_page
INNER JOIN PG_pageFolder on PG_page.folder_id = PG_pageFolder.id
INNER JOIN PG_domain on PG_pageFolder.domain = PG_domain.name
WHERE ({fid} IS NULL OR PG_page.folder_id = {fid});