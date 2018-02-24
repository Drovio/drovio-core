SELECT
GLC_town.id,
GLC_town.description,
GLC_town.latitude,
GLC_town.longitude,
GLC_town.country_id,
GLC_country.countryName
FROM GLC_town
INNER JOIN GLC_country ON GLC_town.country_id = GLC_country.id