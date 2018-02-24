SELECT *
FROM DEV_project
WHERE projectType = 4 AND (projectStatus = 3 OR projectStatus = 4)
ORDER BY title ASC;