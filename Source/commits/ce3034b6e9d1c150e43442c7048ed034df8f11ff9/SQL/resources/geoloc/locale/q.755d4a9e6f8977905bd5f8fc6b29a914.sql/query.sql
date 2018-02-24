-- Remove all defaults
UPDATE GLC_locale
SET GLC_locale.is_default = 0;

-- Set default locale
UPDATE GLC_locale
SET GLC_locale.is_default = 1
WHERE GLC_locale.locale = '$locale';