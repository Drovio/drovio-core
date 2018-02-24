-- Get language
SELECT GLC_language.languageCode_ISO1_A2 INTO @language
FROM GLC_language
WHERE GLC_language.id = $language_id;

-- Get country
SELECT GLC_country.countryCode_ISO2A INTO @country
FROM GLC_country
WHERE GLC_country.id = $country_id;

-- Insert locale
INSERT IGNORE INTO GLC_locale (language_id, country_id, locale, friendlyName, active)
VALUES ($language_id, $country_id, CONCAT(@language, '_', @country), '$friendlyName', 0);