SELECT *
FROM PLM_account
WHERE administrator = 0 AND parent_id IS NOT NULL AND parent_id = {parent};