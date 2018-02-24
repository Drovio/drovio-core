INSERT INTO GLC_country (countryName, countryCode_ISO2A, countryCode_ISO3A, countryCode_UNVehicle, countryCode_ITUCalling, region_id, imageName)
VALUES ('$name', '$iso2a', '$iso3a', '$unvehicle', '$itucall', $region_id, '$imageName');
SELECT LAST_INSERT_ID() AS last_id;