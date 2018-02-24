-- Get generic product id
SELECT RTL_Cproduct.id INTO @productID
FROM RTL_Cproduct
WHERE RTL_Cproduct.company_id = {cid} AND RTL_Cproduct.product_id = {pid};

-- Get product info values
SELECT RTL_CproductInfoValue.*, RTL_productInfo.title AS infoTitle, RTL_productInfo.category_id, RTL_productInfoCategory.title AS categoryTitle
FROM RTL_CproductInfoValue
INNER JOIN RTL_productInfo on RTL_productInfo.id = RTL_CproductInfoValue.info_id
INNER JOIN RTL_productInfoCategory on RTL_productInfo.category_id = RTL_productInfoCategory.id
WHERE RTL_CproductInfoValue.product_id = @productID AND ({iid} IS NULL OR RTL_CproductInfoValue.info_id = {iid});