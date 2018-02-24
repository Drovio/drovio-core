SELECT RB_team.*
FROM RB_team
INNER JOIN PLM_accountToTeam ON PLM_accountToTeam.team_id = RB_team.id
WHERE PLM_accountToTeam.team_id = {tid} AND PLM_accountToTeam.account_id = {aid};