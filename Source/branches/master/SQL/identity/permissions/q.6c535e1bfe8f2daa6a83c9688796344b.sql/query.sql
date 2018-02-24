SELECT ID_PM_group.*
FROM ID_PM_group
INNER JOIN ID_PM_accountGroup ON ID_PM_group.id = ID_PM_accountGroup.group_id
WHERE ID_PM_accountGroup.account_id = {aid};