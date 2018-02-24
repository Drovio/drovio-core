-- Get generic product id
SELECT RTL_Cproduct.id INTO @productID
FROM RTL_Cproduct
WHERE RTL_Cproduct.company_id = {cid} AND RTL_Cproduct.product_id = {cpid};

-- Get product info values
SELECTÂ RTL_productInfoValue.*, RTL_productInfo.title AS infoTitle
FROM RTL_productInfoValue
INNER JOIN RTL_productInfo on RTL_productInfo.id = RTL_productInfoValue.info_id
WHERE
	({gpid} IS NULL OR RTL_productInfoValue.global_product_id = {gpid})
AND	(@productID IS NULL OR RTL_productInfoValue.company_product_id = @productID)
AND	({iid} IS NULL OR RTL_productInfoValue.info_id = {iid});