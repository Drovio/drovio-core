-- Get Literal id from translation
SELECT literal_id, value, locale into @literal_id, @translationValue, @locale
FROM TR_literalTranslation
WHERE TR_literalTranslation.id = $id;

-- Add Locked translation
INSERT INTO TR_literalValue(literal_id, locale, value) VALUES(@literal_id, @locale, @translationValue);

-- Remove all translations
DELETE FROM TR_literalTranslation
WHERE TR_literalTranslation.literal_id = @literal_id;