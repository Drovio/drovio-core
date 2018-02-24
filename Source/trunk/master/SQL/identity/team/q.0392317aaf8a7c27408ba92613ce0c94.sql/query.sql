SELECT ID_team.*
FROM ID_team
INNER JOIN ID_teamAccount ON ID_teamAccount.team_id = ID_team.id
WHERE ID_teamAccount.account_id = {aid}
ORDER BY ID_team.name ASC;