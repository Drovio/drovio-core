DELETE FROM RTL_invoice
WHERE id = '{iid}' AND owner_company_id = {cid} AND completed = 0;