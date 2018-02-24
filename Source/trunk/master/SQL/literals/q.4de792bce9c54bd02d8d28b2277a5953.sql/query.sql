UPDATE TR_literalScope
SET scope = '{newScope}'
WHERE TR_literalScope.project_id = {project_id} AND TR_literalScope.scope = '{scope}';