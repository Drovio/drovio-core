UPDATE UNIT_page SET
	module_id = $mid, 
	file = '$name', 
	folder_id = $fid, 
	static = $static,
	sitemap = $sitemap 
WHERE id = $pid