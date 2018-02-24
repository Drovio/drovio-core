UPDATE RTL_customer
	SET firstname = '{firstname}', lastname = '{lastname}', middle_name = '{middlename}'
WHERE person_id = '{pid}' AND owner_company_id = {cid};