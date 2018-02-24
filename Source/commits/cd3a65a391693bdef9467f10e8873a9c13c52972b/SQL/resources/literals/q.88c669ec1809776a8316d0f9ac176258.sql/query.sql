-- Get Literal id of the given [scope, name] literal
SELECT TR_literal.id INTO @literalID
FROM TR_literal
WHERE TR_literal.scope = '{scope}' AND TR_literal.name = '{name}';

-- Update description (if any)
UPDATE TR_literal
SET TR_literal.description = '{desc}'
WHERE TR_literal.id = @literalID;

-- Update literal value
UPDATE TR_literalValue
SET value = '{value}'
WHERE TR_literalValue.literal_id = @literalID AND TR_literalValue.locale = '{locale}';