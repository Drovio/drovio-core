UPDATE RTL_customer
SET balance = {balance}
WHERE person_id = '{pid}' AND owner_company_id = {cid};