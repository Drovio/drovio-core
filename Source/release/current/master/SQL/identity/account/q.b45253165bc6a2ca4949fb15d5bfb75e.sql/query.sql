UPDATE ID_account
SET
	password = '{password}',
	reset = NULL
WHERE id = {aid};