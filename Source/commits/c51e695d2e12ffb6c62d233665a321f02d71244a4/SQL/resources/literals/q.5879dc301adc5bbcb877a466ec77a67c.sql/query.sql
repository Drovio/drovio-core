SELECT COUNT(*) as count
FROM TR_literal
INNER JOIN TR_literalValue ON TR_literal.id = TR_literalValue.literal_id
WHERE TR_literalValue.locale = "$locale";