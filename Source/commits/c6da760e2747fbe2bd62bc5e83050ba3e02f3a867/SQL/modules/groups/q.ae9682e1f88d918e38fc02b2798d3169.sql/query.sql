-- Get left pointer
SELECT lft INTO @pivot 
FROM MDL_moduleGroup 
WHERE MDL_moduleGroup.id = {parent_id}
LIMIT 1;

-- Get right pointer
SELECT rgt INTO @pivot 
FROM MDL_moduleGroup 
WHERE @pivot IS NULL AND parent_id IS NULL 
ORDER BY rgt DESC 
LIMIT 1;

-- Move all groups to make space
UPDATE MDL_moduleGroup SET rgt = rgt + 2 WHERE rgt > @pivot;
UPDATE MDL_moduleGroup SET lft = lft + 2 WHERE lft > @pivot;

INSERT INTO MDL_moduleGroup(description, lft, rgt, parent_id) VALUES('{description}', @pivot + 1, @pivot + 2, {parent_id});

-- Get group id
SELECT LAST_INSERT_ID() AS last_id;