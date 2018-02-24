UPDATE RTL_Cproduct
SET active = {status}
WHERE company_id = {cid} AND product_id = {pid};