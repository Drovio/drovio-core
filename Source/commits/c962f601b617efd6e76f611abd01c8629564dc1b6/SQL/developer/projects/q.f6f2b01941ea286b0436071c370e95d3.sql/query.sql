SELECT *
FROM DEV_project
WHERE projectType IN (
	SELECT id
	FROM DEV_projectType
	WHERE private = 1
)