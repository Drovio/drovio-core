SELECT moduleGroup.id AS group_id, 
	moduleGroup.description AS group_description, 
	moduleParent.id AS parent_id, 
	moduleParent.description AS parent_description 
FROM UNIT_moduleGroup AS moduleGroup 
INNER JOIN UNIT_moduleGroup AS moduleParent ON moduleGroup.parent_id = moduleParent.id 
WHERE moduleGroup.id = '{id}'