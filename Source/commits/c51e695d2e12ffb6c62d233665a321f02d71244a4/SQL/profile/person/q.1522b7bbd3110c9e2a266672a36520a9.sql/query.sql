SELECT *
FROM RB_person
INNER JOIN PLM_user ON PLM_user.person_id = RB_person.id
WHERE PLM_user.username = '$username'