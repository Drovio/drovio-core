SELECT *
FROM RB_apps
WHERE RB_apps.authorID = $accountID
ORDER BY RB_apps.fullName ASC;