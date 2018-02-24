SELECT RTL_productHierarchy.*
FROM RTL_productHierarchy
INNER JOIN RTL_productInfoCategoryHierarchy ON RTL_productInfoCategoryHierarchy.id = RTL_productHierarchy.hierarchy_id
WHERE RTL_productInfoCategoryHierarchy.category_id = {cid};