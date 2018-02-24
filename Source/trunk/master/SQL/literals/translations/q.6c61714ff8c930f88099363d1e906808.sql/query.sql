-- Get the literal proposal with the biggest score ( = votes.positive - votes.negative)
SELECT
	TR_literalScope.scope,
	TR_literal.name,
	TR_literalTranslation.value
FROM TR_literalTranslation
INNER JOIN TR_literal ON TR_literal.id = TR_literalTranslation.literal_id
INNER JOIN TR_literalScope ON TR_literalScope.id = TR_literal.scope_id
WHERE TR_literalTranslation.literal_id IN (
	-- Get all literals of the given scope
	SELECT TR_literal.id
	FROM TR_literal
	INNER JOIN TR_literalScope ON TR_literalScope.id = TR_literal.scope_id
	WHERE TR_literalScope.project_id = {project_id} AND TR_literalScope.scope = '{scope}'
) AND TR_literalTranslation.locale = '{locale}'
ORDER BY TR_literalTranslation.score DESC
LIMIT 1;