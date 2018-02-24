-- Get Literal id of the given [scope, name] literal
SELECT TR_literal.id INTO @literal_id
FROM TR_literal
INNER JOIN TR_literalScope ON TR_literalScope.id = TR_literal.scope_id
WHERE TR_literalScope.project_id = {project_id} AND TR_literalScope.scope = '{scope}' AND TR_literal.name = '{name}';

-- Clear translations
DELETE FROM TR_literalTranslation
WHERE TR_literalTranslation.literal_id = @literal_id;