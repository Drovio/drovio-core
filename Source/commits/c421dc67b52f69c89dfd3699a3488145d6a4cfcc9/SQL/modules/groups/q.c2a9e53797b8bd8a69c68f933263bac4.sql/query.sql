-- Get group pointers
SELECT @myLeft := lft, @myRight := rgt 
FROM UNIT_moduleGroup 
WHERE UNIT_moduleGroup.id = {id};

-- Delete group
DELETE FROM UNIT_moduleGroup WHERE lft BETWEEN @myLeft AND @myRight;

-- Update all pointers
UPDATE UNIT_moduleGroup SET rgt = rgt - 2 WHERE rgt > @myRight;
UPDATE UNIT_moduleGroup SET lft = lft - 2 WHERE lft > @myRight;