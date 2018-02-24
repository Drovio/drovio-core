-- Get scope id
SELECT id INTO @scopeID
FROM TR_literalScope
WHERE TR_literalScope.project_id = {project_id} AND TR_literalScope.scope = '{scope}';

-- Remove literal
DELETE FROM TR_literal
WHERE TR_literal.scope_id = @scopeID AND TR_literal.name = '{name}'