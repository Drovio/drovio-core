SELECT @myLeft := lft, @myRight := rgt 
FROM UNIT_pageFolder 
WHERE UNIT_pageFolder.id = $id;
DELETE FROM UNIT_pageFolder WHERE lft BETWEEN @myLeft AND @myRight;
UPDATE UNIT_pageFolder SET rgt = rgt - 2 WHERE rgt &gt; @myRight;
UPDATE UNIT_pageFolder SET lft = lft - 2 WHERE lft &gt; @myRight;