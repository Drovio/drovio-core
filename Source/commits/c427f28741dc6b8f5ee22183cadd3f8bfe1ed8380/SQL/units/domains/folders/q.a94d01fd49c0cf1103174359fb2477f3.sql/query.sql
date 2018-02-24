SELECT {parent_id} INTO @parent;

SELECT lft INTO @pivot 
FROM UNIT_pageFolder 
WHERE UNIT_pageFolder.id = @parent
LIMIT 1;

SELECT rgt INTO @pivot 
FROM UNIT_pageFolder 
WHERE @pivot IS NULL AND parent_id IS NULL 
ORDER BY rgt DESC 
LIMIT 1;

SELECT IF(ISNULL(@parent), 1, 0) INTO @root;

UPDATE UNIT_pageFolder SET rgt = rgt + 2 WHERE rgt > @pivot;
UPDATE UNIT_pageFolder SET lft = lft + 2 WHERE lft > @pivot;
INSERT INTO UNIT_pageFolder(name, domain, is_root, lft, rgt, parent_id) VALUES('{name}', '{domain}', @root, @pivot + 1, @pivot + 2, @parent);

SELECT LAST_INSERT_ID() AS last_id;