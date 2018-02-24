SELECT RTL_productInfo.*, RTL_productInfoCategory.title AS categoryTitle
FROM RTL_productInfo
LEFT OUTER JOIN RTL_productInfoCategory on RTL_productInfoCategory.id = RTL_productInfo.category_id
WHERE ({catid} IS NULL OR RTL_productInfo.category_id = {catid}) AND (RTL_productInfo.company_id IS NULL OR RTL_productInfo.company_id = {cid});