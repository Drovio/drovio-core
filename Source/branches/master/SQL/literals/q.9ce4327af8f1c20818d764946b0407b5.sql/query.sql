-- Get scope id
SELECT id INTO @scopeID
FROM TR_literalScope
WHERE project_id = {project_id} AND scope = '{scope}';

-- Create literal
INSERT INTO TR_literal(name, description, scope_id, project_id)
VALUES('{name}', '{desc}', @scopeID, {project_id});

-- Get literal id
SELECT LAST_INSERT_ID() INTO @id;

/* Add literal value for the specified locale */
INSERT INTO TR_literalValue(literal_id, locale, value)
VALUES(@id, '{locale}', '{value}');