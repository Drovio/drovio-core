UPDATE RTL_customer
SET
	is_company = '{is_company}',
	company_name = '{cname}',
	tax_id = '{taxid}',
	irs = '{irs}'
WHERE person_id = '{pid}' AND owner_company_id = {cid};