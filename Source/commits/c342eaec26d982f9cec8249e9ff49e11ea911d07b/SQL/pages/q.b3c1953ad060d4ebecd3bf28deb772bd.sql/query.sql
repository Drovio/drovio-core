UPDATE PG_page SET
	file = '{name}',
	folder_id = {fid},
	static = {static},
	sitemap = {sitemap},
	attributes = '{attributes}'
WHERE id = {pid}