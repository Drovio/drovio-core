SELECT 
	TR_literal.id, 
	TR_literal.name, 
	TR_literal.description, 
	TR_literal.scope, 
	TR_literalValue.value 
FROM TR_literal
INNER JOIN TR_literalValue ON TR_literalValue.literal_id = TR_literal.id
WHERE TR_literal.project_id = '{project_id}' AND TR_literal.scope = '{scope}' AND TR_literalValue.locale = '{locale}'
ORDER BY TR_literal.name ASC