SELECT COUNT(DISTINCT TR_literalTranslation.literal_id) as count
FROM TR_literalTranslation
WHERE TR_literalTranslation.locale = "$locale";