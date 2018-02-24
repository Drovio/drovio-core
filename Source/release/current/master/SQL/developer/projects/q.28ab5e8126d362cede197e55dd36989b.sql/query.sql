UPDATE DEV_project
SET
	title = '{title}', 
	description = '{description}',
	open = {open},
	public = {public}
WHERE id = {id};