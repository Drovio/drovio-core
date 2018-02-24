UPDATE DEV_project
SET
	title = '{title}', 
	description = '{description}', 
	editorSub = '{editorSub}', 
	editorPath = '{editorPath}'
WHERE id = {id};