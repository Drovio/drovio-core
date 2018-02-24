SELECT
	moduleGroup.id,
	moduleGroup.description, 
	moduleGroup.id AS group_id, 
	moduleGroup.description AS group_description, 
	moduleParent.id AS parent_id, 
	moduleParent.description AS parent_description 
FROM MDL_moduleGroup AS moduleGroup 
LEFT OUTER JOIN MDL_moduleGroup AS moduleParent ON moduleGroup.parent_id = moduleParent.id;