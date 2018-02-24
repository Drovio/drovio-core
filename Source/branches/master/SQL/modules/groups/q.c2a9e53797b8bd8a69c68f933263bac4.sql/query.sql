-- Get group pointers
SELECT @myLeft := lft, @myRight := rgt 
FROM MDL_moduleGroup 
WHERE MDL_moduleGroup.id = {id};

-- Delete group
DELETE FROM MDL_moduleGroup WHERE lft BETWEEN @myLeft AND @myRight;

-- Update all pointers
UPDATE MDL_moduleGroup SET rgt = rgt - 2 WHERE rgt > @myRight;
UPDATE MDL_moduleGroup SET lft = lft - 2 WHERE lft > @myRight;