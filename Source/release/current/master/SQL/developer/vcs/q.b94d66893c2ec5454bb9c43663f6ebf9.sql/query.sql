INSERT INTO VCS_projectWorkingAuthor (project_id, author_id, working_branch)
VALUES ({pid}, {author}, '{branch}')
ON DUPLICATE KEY UPDATE working_branch = '{branch}';