-- Update scope
UPDATE TR_literalScope
SET scope = '{newScope}'
WHERE TR_literalScope.project_id = '{project_id}' AND TR_literalScope.scope = '{scope}'

-- Update literals in the same scope
UPDATE TR_literal
SET scope = '{newScope}'
WHERE TR_literal.project_id = '{project_id}' AND TR_literal.scope = '{scope}'