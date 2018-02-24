-- Get left and right
SELECT @myLeft := lft, @myRight := rgt 
FROM PG_pageFolder 
WHERE PG_pageFolder.id = {id};

-- Remove folder
DELETE FROM PG_pageFolder WHERE lft BETWEEN @myLeft AND @myRight;

-- Update other records
UPDATE PG_pageFolder SET rgt = rgt - 2 WHERE rgt > @myRight;
UPDATE PG_pageFolder SET lft = lft - 2 WHERE lft > @myRight;