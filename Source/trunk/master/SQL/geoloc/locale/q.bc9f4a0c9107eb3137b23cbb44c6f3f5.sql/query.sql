SELECT * 
FROM GLC_locale 
INNER JOIN GLC_country ON GLC_locale.country_id = GLC_country.id 
INNER JOIN GLC_language ON GLC_locale.language_id = GLC_language.id 
WHERE GLC_locale.locale = '{locale}';