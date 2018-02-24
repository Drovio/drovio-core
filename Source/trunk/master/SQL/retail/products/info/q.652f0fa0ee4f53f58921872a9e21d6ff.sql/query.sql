SELECT RTL_productInfoValue.*, RTL_productInfo.title AS infoTitle, RTL_productInfo.category_id, RTL_productInfoCategory.title AS categoryTitle
FROM RTL_productInfoValue
INNER JOIN RTL_productInfo on RTL_productInfo.id = RTL_productInfoValue.info_id
INNER JOIN RTL_productInfoCategory on RTL_productInfo.category_id = RTL_productInfoCategory.id
WHERE RTL_productInfoValue.product_id = {pid} AND ({iid} IS NULL OR RTL_productInfoValue.info_id = {iid});