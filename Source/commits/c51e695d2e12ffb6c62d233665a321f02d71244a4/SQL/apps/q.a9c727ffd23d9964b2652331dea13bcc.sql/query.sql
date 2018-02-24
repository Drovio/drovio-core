UPDATE RB_apps SET
	name = IF('$name' = "", NULL, '$name'), 
	fullName = '$fullName', 
	tags = '$tags', 
	description = '$description', 
	scope = '$scope'
WHERE RB_apps.id = $id;