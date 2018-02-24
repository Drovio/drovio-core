SELECT *
FROM RTL_invoice
WHERE owner_company_id = {cid} AND time_created >= {ftime} AND time_created <= {ttime} AND completed = 0 AND ({aid} IS NULL OR account_ID = {aid});