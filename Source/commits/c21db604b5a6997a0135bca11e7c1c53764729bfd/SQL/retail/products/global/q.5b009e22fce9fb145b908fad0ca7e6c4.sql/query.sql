SELECT RTL_product.*, RTL_productHierarchy.title AS hierarchy
FROM RTL_product
INNER JOIN RTL_productHierarchy ON RTL_productHierarchy.id = RTL_product.hierarchy_id
WHERE RTL_product.id = {id};