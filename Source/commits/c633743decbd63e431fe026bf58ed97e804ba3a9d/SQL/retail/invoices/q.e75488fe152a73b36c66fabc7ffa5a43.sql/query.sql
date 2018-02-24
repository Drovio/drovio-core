UPDATE RTL_invoice
SET seller_info = '{sinfo}'
WHERE id = '{iid}' AND owner_company_id = {cid};