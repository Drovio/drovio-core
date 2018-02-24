UPDATE RB_person 
SET
	firstname = '$firstname', 
	lastname = '$lastname'
WHERE RB_person.id = $pid;