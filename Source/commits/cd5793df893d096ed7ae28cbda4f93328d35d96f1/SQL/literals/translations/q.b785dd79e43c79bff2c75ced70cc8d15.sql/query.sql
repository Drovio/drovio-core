-- Get Literal id of the given [scope, name] literal
SELECT TR_literal.id INTO @literal_id
FROM TR_literal
WHERE TR_literal.project_id = '{project_id}' AND TR_literal.scope = '{scope}' AND TR_literal.name = '{name}';

-- Clear translations
DELETE FROM TR_literalTranslation
WHERE TR_literalTranslation.literal_id = @literal_id;

-- Clear locked literals in other locale
DELETE FROM TR_literalValue
WHERE TR_literalValue.literal_id = @literal_id AND TR_literalValue.locale != '{locale}';