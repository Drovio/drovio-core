-- Get the literal proposal with the biggest score ( = votes.positive - votes.negative)
SELECT
	TR_literal.scope,
	TR_literal.name,
	TR_literalTranslation.value
FROM TR_literalTranslation
INNER JOIN TR_literal ON TR_literal.id = TR_literalTranslation.literal_id
WHERE TR_literalTranslation.literal_id IN (
	-- Get all literals of the given scope
	SELECT TR_literal.id
	FROM TR_literal
	WHERE TR_literal.project_id = '{project_id}' AND TR_literal.scope = '{scope}'
) AND TR_literalTranslation.locale = '{locale}'
ORDER BY TR_literalTranslation.score DESC
LIMIT 1;