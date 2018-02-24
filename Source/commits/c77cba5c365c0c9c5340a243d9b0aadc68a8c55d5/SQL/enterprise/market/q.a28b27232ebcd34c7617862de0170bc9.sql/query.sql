SELECT application_id, version, count(team_id) as team_count
FROM BSS_app_purchase
WHERE application_id = {id}
GROUP BY version
ORDER BY version DESC;