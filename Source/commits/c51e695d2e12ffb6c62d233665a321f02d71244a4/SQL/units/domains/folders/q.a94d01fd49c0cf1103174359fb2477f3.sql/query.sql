SELECT lft INTO @pivot 
FROM UNIT_pageFolder 
WHERE UNIT_pageFolder.id = $parent_id 
LIMIT 1;

SELECT rgt INTO @pivot 
FROM UNIT_pageFolder 
WHERE @pivot IS NULL AND parent_id IS NULL 
ORDER BY rgt DESC 
LIMIT 1;

SELECT IF(ISNULL($parent_id), 1, 0) INTO @root;

SELECT $parent_id INTO @parent;
UPDATE UNIT_pageFolder SET rgt = rgt + 2 WHERE rgt &gt; @pivot;
UPDATE UNIT_pageFolder SET lft = lft + 2 WHERE lft &gt; @pivot;
INSERT INTO UNIT_pageFolder(name, domain, is_root, lft, rgt, parent_id) VALUES('$name', '$domain', @root, @pivot + 1, @pivot + 2, @parent);

SELECT LAST_INSERT_ID() AS last_id;