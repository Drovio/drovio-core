UPDATE RTL_Cproduct
SET taxRate_id = {rate}
WHERE RTL_Cproduct.company_id = {cid} AND RTL_Cproduct.product_id = {pid};