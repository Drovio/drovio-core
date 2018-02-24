SELECT
	TR_translator.*,
	GLC_locale.friendlyName
FROM TR_translator
INNER JOIN GLC_locale ON GLC_locale.locale = TR_translator.translation_locale
WHERE TR_translator.id = $id