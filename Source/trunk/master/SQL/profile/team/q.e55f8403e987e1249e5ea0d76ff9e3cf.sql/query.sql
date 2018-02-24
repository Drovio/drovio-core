SELECT PLM_account.*
FROM PLM_account
INNER JOIN PLM_accountToTeam ON PLM_accountToTeam.account_id = PLM_account.id
WHERE PLM_accountToTeam.team_id = {tid};