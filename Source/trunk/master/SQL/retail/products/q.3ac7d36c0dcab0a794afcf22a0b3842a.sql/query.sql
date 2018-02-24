UPDATE RTL_product
SET
	title = '{title}',
	notes = '{notes}'
WHERE owner_company_id = {cid} AND id = '{pid}';