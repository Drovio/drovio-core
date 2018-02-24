SELECT 
	TR_literal.id, 
	TR_literal.name, 
	TR_literal.description,
	TR_literalScope.scope,
	TR_literalValue.value 
FROM TR_literal
INNER JOIN TR_literalScope ON TR_literalScope.id = TR_literal.scope_id
INNER JOIN TR_literalValue ON TR_literalValue.literal_id = TR_literal.id
WHERE TR_literalScope.project_id = {project_id} AND TR_literalScope.scope = '{scope}' AND TR_literalValue.locale = '{locale}'
ORDER BY TR_literal.name ASC