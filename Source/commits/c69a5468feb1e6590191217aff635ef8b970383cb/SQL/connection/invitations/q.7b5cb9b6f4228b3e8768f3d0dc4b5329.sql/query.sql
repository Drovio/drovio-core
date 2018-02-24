SELECT *, RB_invitationType.name as type_name, RB_invitationType.group_id
FROM RB_invitation
INNER JOIN RB_invitationType ON RB_invitationType.id = RB_invitation.type
WHERE context = '{context}' AND type = {type};