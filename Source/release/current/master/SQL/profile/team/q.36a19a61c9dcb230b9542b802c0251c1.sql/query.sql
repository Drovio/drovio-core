SELECT PLM_account.*
FROM PLM_account
INNER JOIN PLM_accountToTeam ON PLM_accountToTeam.account_id = PLM_account.id
WHERE PLM_accountToTeam.team_id = {tid}

UNION

SELECT managedAccount.*
FROM PLM_account AS managedAccount
INNER JOIN PLM_account AS adminAccount ON managedAccount.parent_id = adminAccount.id
INNER JOIN PLM_accountToTeam ON PLM_accountToTeam.account_id = adminAccount.id
WHERE PLM_accountToTeam.team_id = {tid}