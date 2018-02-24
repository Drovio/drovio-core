-- Get lft pointer
SELECT lft INTO @pivot 
FROM RTL_productHierarchy 
WHERE RTL_productHierarchy.id = {pid}
LIMIT 1;


-- Get rgt pointer
SELECT rgt INTO @pivot 
FROM RTL_productHierarchy
WHERE @pivot IS NULL AND parent_id IS NULL 
ORDER BY rgt DESC 
LIMIT 1;

-- Move all records to make space
UPDATE RTL_productHierarchy SET rgt = rgt + 2 WHERE rgt > @pivot;
UPDATE RTL_productHierarchy SET lft = lft + 2 WHERE lft > @pivot;

-- Create record
INSERT INTO RTL_productHierarchy (title, description, lft, rgt, parent_id, company_id)
VALUES('{title}', '{desc}', @pivot + 1, @pivot + 2, {pid}, {cid});

-- Get record id
SELECT LAST_INSERT_ID() AS id;