UPDATE RB_person 
SET defaultAddress_id = $addrid
WHERE RB_person.id = ( 
	SELECT PLM_user.person_id 
	FROM PLM_user 
	WHERE PLM_user.id = $uid)