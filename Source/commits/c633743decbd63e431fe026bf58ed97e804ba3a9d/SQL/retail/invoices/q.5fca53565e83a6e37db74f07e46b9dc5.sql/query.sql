UPDATE RTL_invoice
SET notes = '{notes}'
WHERE id = '{iid}' AND owner_company_id = {cid};