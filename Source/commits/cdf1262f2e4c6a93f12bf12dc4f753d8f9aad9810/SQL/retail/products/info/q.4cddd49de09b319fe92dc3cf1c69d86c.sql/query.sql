SELECT RTL_productInfoCategory.*
FROM RTL_productInfoCategory
LEFT OUTER JOIN RTL_productInfoCategoryHierarchy on RTL_productInfoCategory.id = RTL_productInfoCategoryHierarchy.category_id
WHERE ({hid} IS NULL OR RTL_productInfoCategoryHierarchy.hierarchy_id = {hid}) AND (RTL_productInfoCategory.company_id IS NULL ORÂ RTL_productInfoCategory.company_id = {cid});