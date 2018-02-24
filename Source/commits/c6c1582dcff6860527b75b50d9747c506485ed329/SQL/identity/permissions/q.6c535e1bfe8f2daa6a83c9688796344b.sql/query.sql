SELECT ID_PM_group.*
FROM ID_PM_group
INNER JOIN ID_PM_userGroup ON ID_PM_group.id = ID_PM_userGroup.group_id
WHERE ID_PM_userGroup.account_id = {aid};