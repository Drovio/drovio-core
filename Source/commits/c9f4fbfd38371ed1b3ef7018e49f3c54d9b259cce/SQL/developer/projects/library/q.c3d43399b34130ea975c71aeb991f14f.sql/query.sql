SELECT
	DEV_projectRelease.*,
	RB_team.id AS team_id,
	RB_team.name AS teamName,
	DEV_project.title AS projectTitle,
	DEV_project.description AS projectDescription,
	DEV_project.description
FROM DEV_projectRelease
INNER JOIN DEV_project ON DEV_project.id = DEV_projectRelease.project_id
INNER JOIN RB_team ON RB_team.id = DEV_project.team_id
WHERE DEV_projectRelease.project_id = {pid} AND DEV_projectRelease.version = '{version}';