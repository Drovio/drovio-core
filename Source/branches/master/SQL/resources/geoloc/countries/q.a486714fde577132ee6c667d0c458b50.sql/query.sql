UPDATE GLC_country SET 
countryName = '$name', 
countryCode_ISO2A = '$iso2a', 
countryCode_ISO3A = '$iso3a', 
countryCode_UNVehicle = '$unvehicle', 
countryCode_ITUCalling = '$itucall', 
region_id = $region_id, 
imageName = '$imageName'
WHERE GLC_country.id = $id