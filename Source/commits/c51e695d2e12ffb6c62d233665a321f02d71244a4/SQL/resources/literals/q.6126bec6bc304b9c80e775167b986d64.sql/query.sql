-- Create literal
INSERT INTO TR_literal(name, description, scope, static)
VALUES('$name', '$desc', '$scope', $static);

-- Get literal id
SELECT LAST_INSERT_ID() INTO @id;

-- Add literal value for the specified locale
INSERT INTO TR_literalValue(literal_id, locale, value)
VALUES(@id, '$locale', '$value');