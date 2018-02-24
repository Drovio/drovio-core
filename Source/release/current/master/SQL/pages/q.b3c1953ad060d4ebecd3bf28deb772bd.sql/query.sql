UPDATE PG_page SET
	file = '{name}',
	folder_id = {fid},
	sitemap = {sitemap}
WHERE id = {pid}