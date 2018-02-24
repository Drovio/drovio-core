DELETE FROM TR_literal
INNER JOIN TR_literalScope ON TR_literalScope.id = TR_literal.scope_id
WHERE TR_literalScope.project_id = {project_id} AND TR_literalScope.scope = '{scope}' AND TR_literal.name = '{name}'