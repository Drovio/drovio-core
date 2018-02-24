UPDATE ID_account
SET
	password = '{password}'
WHERE id = {aid} AND administrator = 0 AND parent_id IS NOT NULL AND parent_id = {parent_id};