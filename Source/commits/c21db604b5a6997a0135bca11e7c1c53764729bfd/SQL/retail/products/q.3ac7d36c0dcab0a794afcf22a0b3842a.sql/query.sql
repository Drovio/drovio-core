UPDATE RTL_Cproduct
SET title = '{title}', description = '{description}'
WHERE company_id = {cid} AND product_id = {pid};