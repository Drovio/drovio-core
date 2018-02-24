SELECT ID_account.*
FROM ID_account
INNER JOIN ID_teamAccount ON ID_teamAccount.account_id = ID_account.id
WHERE ID_teamAccount.team_id = {tid};