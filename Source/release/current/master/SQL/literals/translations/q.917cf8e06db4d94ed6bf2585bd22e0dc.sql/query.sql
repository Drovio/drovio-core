-- Get Literal id of the given [scope, name] literal
SELECT TR_literal.id INTO @literal_id
FROM TR_literal
INNER JOIN TR_literalScope ON TR_literalScope.id = TR_literal.scope_id
WHERE TR_literalScope.project_id = {project_id} AND TR_literalScope.scope = '{scope}' AND TR_literal.name = '{name}';

-- Insert OR Update Translation
INSERT INTO TR_literalTranslation (literal_id, translator_id, locale, value)
VALUES (@literal_id, {translator_id}, '{locale}', '{value}')
	ON DUPLICATE KEY UPDATE value = '{value}';