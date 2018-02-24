UPDATE RTL_invoice
SET
	supplier_id = NULL,
	customer_id = '{cust_id}'
WHERE id = '{iid}' AND owner_company_id = {cid};