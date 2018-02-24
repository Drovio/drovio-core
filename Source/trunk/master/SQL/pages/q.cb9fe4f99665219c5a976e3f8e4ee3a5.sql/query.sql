UPDATE PG_page SET
	module_id = {mid},
	static = {static},
	attributes = '{attributes}'
WHERE id = {pid}