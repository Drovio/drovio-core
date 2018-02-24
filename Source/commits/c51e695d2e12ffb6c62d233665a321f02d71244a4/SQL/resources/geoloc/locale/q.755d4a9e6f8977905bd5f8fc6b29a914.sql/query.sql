-- Remove all defaults
UPDATE GLC_locale
SET GLC_locale.default = 0;

-- Set default locale
UPDATE GLC_locale
SET GLC_locale.default = 1
WHERE GLC_locale.locale = '$locale';