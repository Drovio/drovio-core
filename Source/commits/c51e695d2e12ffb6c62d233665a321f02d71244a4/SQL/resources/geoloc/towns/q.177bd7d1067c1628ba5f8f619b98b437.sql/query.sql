UPDATE GLC_town SET 
description = '$description', 
country_id = $countryID, 
latitude = $latitude, 
longitude = $longitude
WHERE GLC_town.id = $id