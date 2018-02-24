-- Get Literal id of the given [scope, name] literal
SELECT TR_literal.id INTO @literal_id
FROM TR_literal
WHERE TR_literal.scope = '$scope' AND TR_literal.name = '$name';

-- Get Literal translations
-- OUTER LEFT JOIN TR_literalTranslationVote ON TR_literalTranslation.id = TR_literalTranslationVote.translation_id
-- AND TR_literalTranslationVote.translator_id = $translator_id
SELECT *
FROM TR_literalTranslation
WHERE TR_literalTranslation.literal_id = @literal_id AND TR_literalTranslation.locale = '$locale';