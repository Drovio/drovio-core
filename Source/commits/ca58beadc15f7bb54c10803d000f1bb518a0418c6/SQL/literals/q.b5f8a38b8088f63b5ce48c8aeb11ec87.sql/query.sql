-- Get scope id
SELECT id INTO @scopeID
FROM TR_literalScope
WHERE TR_literalScope.project_id = {project_id} AND TR_literalScope.scope = '{scope}';

-- Get literal id
SELECT id INTO @literalID
FROM TR_literal
WHERE TR_literal.scope_id = @scopeID AND TR_literal.name = '{name}';

-- Remove literal value
DELETE FROM TR_literalValue
WHERE TR_literalValue.literal_id = @literalID AND TR_literalValue.locale = '{locale}';