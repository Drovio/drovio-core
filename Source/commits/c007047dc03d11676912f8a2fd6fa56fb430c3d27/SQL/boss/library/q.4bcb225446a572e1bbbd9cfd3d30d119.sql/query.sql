SELECT
	BSS_app_purchase.*,
	RB_team.name AS teamName,
	DEV_projectRelease.title,
	DEV_projectRelease.changelog,
	(
		SELECT DEV_projectRelease.version
		FROM DEV_projectRelease
		WHERE DEV_projectRelease.project_id = DEV_project.id AND DEV_projectRelease.status_id = 2 AND DEV_project.online = 1
		ORDER BY time_created DESC
		LIMIT 0,1
	) AS lastVersion,
	(
		SELECT DEV_projectRelease.changelog
		FROM DEV_projectRelease
		WHERE DEV_projectRelease.project_id = DEV_project.id AND DEV_projectRelease.status_id = 2 AND DEV_project.online = 1
		ORDER BY time_created DESC
		LIMIT 0,1
	) AS lastChangelog
FROM BSS_app_purchase
INNER JOIN DEV_project ON DEV_project.id = BSS_app_purchase.application_id
INNER JOIN DEV_projectRelease ON DEV_projectRelease.project_id = BSS_app_purchase.application_id
INNER JOIN RB_team ON RB_team.id = DEV_project.team_id
WHERE BSS_app_purchase.team_id = 2 AND DEV_projectRelease.version = BSS_app_purchase.version;