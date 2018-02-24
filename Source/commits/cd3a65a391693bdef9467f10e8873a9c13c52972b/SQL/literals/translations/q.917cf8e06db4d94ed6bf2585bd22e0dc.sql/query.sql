-- Get Literal id of the given [scope, name] literal
SELECT TR_literal.id INTO @literal_id
FROM TR_literal
WHERE TR_literal.project_id = '{project_id}' AND TR_literal.scope = '{scope}' AND TR_literal.name = '{name}';

-- Get Translator locale
SELECT TR_translator.translation_locale INTO @translation_locale
FROM TR_translator
WHERE TR_translator.id = {translator_id};

-- Insert OR Update Translation
INSERT INTO TR_literalTranslation (literal_id, translator_id, locale, value)
VALUES (@literal_id, {translator_id}, @translation_locale, '{value}')
	ON DUPLICATE KEY UPDATE value = '{value}';