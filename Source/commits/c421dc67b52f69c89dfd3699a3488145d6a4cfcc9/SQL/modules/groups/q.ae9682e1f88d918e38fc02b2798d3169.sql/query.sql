-- Get left pointer
SELECT lft INTO @pivot 
FROM UNIT_moduleGroup 
WHERE UNIT_moduleGroup.id = {parent_id}
LIMIT 1;

-- Get right pointer
SELECT rgt INTO @pivot 
FROM UNIT_moduleGroup 
WHERE @pivot IS NULL AND parent_id IS NULL 
ORDER BY rgt DESC 
LIMIT 1;

-- Move all groups to make space
UPDATE UNIT_moduleGroup SET rgt = rgt + 2 WHERE rgt > @pivot;
UPDATE UNIT_moduleGroup SET lft = lft + 2 WHERE lft > @pivot;

INSERT INTO UNIT_moduleGroup(description, lft, rgt, parent_id) VALUES('{description}', @pivot + 1, @pivot + 2, {parent_id});

-- Get group id
SELECT LAST_INSERT_ID() AS last_id;