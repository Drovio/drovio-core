INSERT INTO event_aggregate(event_name, timespan, user_id, session_id, city, region, country, browser, browser_version, device, current_url, initial_referrer, initial_referrer_domain, operating_system, referrer, referring_domain, screen_height, screen_width, utm_parameters) 
VALUES ('{event_name}', '{timespan}', '{user_id}', '{session_id}',
	IF('{city}' = 'NULL', NULL, '{city}'), 
	IF('{region}' = 'NULL', NULL, '{region}'), 
	IF('{country}' = 'NULL', NULL, '{country}'), 
	IF('{browser}' = 'NULL', NULL, '{browser}'), 
	IF('{browser_version}' = 'NULL', NULL, '{browser_version}'), 
	IF('{device}' = 'NULL', NULL, '{device}'), 
	IF('{current_url}' = 'NULL', NULL, '{current_url}'), 
	IF('{initial_referrer}' = 'NULL', NULL, '{initial_referrer}'), 
	IF('{initial_referrer_domain}' = 'NULL', NULL, '{initial_referrer_domain}'), 
	IF('{operating_system}' = 'NULL', NULL, '{operating_system}'), 
	IF('{referrer}' = 'NULL', NULL, '{referrer}'),
	IF('{referring_domain}' = 'NULL', NULL, '{referring_domain}'),
	IF('{screen_height}' = 'NULL', NULL, '{screen_height}'),
	IF('{screen_width}' = 'NULL', NULL, '{screen_width}'),
	IF('{utm_parameters}' = 'NULL', NULL, '{utm_parameters}'))
ON DUPLICATE KEY UPDATE counter=counter+1