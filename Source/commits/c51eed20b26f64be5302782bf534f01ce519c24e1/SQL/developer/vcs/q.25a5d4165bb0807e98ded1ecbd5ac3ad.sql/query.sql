INSERT INTO VCS_projectItem (id, project_id, path, name, smart)
VALUES ('{id}', {pid}, '{path}', '{name}', {smart})
ON DUPLICATE KEY UPDATE deleted = 0;