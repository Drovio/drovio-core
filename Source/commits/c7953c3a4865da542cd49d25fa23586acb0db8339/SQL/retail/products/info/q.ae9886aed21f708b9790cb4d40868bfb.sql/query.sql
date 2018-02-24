-- Get generic product id
SELECT RTL_Cproduct.id INTO @productID
FROM RTL_Cproduct
WHERE RTL_Cproduct.company_id = {cid} AND RTL_Cproduct.product_id = {cpid};

-- Get product info values
SELECT RTL_productInfoValue.*, RTL_productInfo.title AS infoTitle, RTL_productInfoCategory.title AS categoryTitle
FROM RTL_productInfoValue
INNER JOIN RTL_productInfo on RTL_productInfo.id = RTL_productInfoValue.info_id
INNER JOIN RTL_productInfoCategory on RTL_productInfo.category_id = RTL_productInfoCategory.id
WHERE
	({gpid} IS NULL OR RTL_productInfoValue.global_product_id = {gpid})
AND	(@productID IS NULL OR RTL_productInfoValue.company_product_id = @productID)
AND	({iid} IS NULL OR RTL_productInfoValue.info_id = {iid});