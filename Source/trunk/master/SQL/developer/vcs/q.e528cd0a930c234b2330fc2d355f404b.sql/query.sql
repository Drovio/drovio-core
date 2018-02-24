SELECT working_branch
FROM VCS_projectWorkingAuthor
WHERE project_id = {pid} AND author_id = {author};