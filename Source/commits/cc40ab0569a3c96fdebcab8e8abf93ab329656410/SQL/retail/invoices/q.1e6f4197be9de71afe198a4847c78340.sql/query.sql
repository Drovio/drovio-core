UPDATE RTL_invoice
SET completed = 1
WHERE id = '{iid}' AND owner_company_id = {cid};