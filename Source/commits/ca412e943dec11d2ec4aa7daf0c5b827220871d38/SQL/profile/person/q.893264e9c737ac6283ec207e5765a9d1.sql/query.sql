UPDATE RB_person 
SET
	firstname = '{firstname}', 
	middle_name = '{middle_name}', 
	lastname = '{lastname}', 
	alt_name = '{alt_name}'
WHERE RB_person.id = {pid};