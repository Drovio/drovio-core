UPDATE DEV_project
SET
	repository = '{repository}',
	editorSub = '{editorSub}', 
	editorPath = '{editorPath}'
WHERE id = {id};