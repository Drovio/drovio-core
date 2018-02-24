-- Try to join Translator group stats
INSERT IGNORE INTO TR_translator (user_id, translation_locale)
VALUES ($user_id, '$locale');

-- Update active status (in case there was a deactivated record)
UPDATE TR_translator
SET active = 1
WHERE TR_translator.user_id = $user_id;