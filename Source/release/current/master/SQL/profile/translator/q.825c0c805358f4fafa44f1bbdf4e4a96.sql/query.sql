-- Try to join Translator group stats
INSERT INTO TR_translator (id, translation_locale)
VALUES ($id, '$locale')
	ON DUPLICATE KEY UPDATE translation_locale = '$locale';

-- Update active status (in case there was a deactivated record)
UPDATE TR_translator
SET active = 1
WHERE TR_translator.id = $id;