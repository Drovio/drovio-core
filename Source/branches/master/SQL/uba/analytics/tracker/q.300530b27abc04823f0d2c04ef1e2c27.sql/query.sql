INSERT INTO `users`(`joindate`, `intial_platform`, `initial_device_type`, `initial_country`, `initial_region`, `initial_city`, `platform`, `intial_referrer`, `initial_browser`, `intial_landing_page`, `initial_device`, `initial_carrier`) VALUES (
	IF('{joindate}' = 'NULL', NULL, '{joindate}'),
	IF('{intial_platform}' = 'NULL', NULL, '{intial_platform}'), 
	IF('{initial_device_type}' = 'NULL', NULL, '{initial_device_type}'), 
	IF('{initial_country}' = 'NULL', NULL, '{initial_country}'), 
	IF('{initial_region}' = 'NULL', NULL, '{initial_region}'), 
	IF('{initial_city}' = 'NULL', NULL, '{initial_city}'), 
	IF('{platform}' = 'NULL', NULL, '{platform}'), 
	IF('{intial_referrer}' = 'NULL', NULL, '{intial_referrer}'), 
	IF('{initial_browser}' = 'NULL', NULL, '{initial_browser}'), 
	IF('{intial_landing_page}' = 'NULL', NULL, '{intial_landing_page}'), 
	IF('{initial_device}' = 'NULL', NULL, '{initial_device}'), 
	IF('{initial_carrier}' = 'NULL', NULL, '{initial_carrier}'), )