SELECT *
FROM ENP_infoField
WHERE owner_company_id IS NULL OR owner_company_id = {tid};