UPDATE API_key
SET
	previous_akey = akey,
	akey = '{new_akey}',
	time_expires = {expires}
WHERE akey = '{akey}';