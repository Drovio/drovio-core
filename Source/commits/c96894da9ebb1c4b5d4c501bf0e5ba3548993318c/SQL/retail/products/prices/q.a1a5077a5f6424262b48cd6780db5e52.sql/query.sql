SELECT *
FROM RTL_productPriceType
WHERE company_id IS NULL OR company_id = {cid};