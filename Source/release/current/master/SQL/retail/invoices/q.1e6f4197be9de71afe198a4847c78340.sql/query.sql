UPDATE RTL_invoice
SET
	completed = 1,
	total_price = {total_price},
	total_tax = {total_tax},
	total_payments = {total_payments}
WHERE id = '{iid}' AND owner_company_id = {cid};