SELECT
PLM_user.id AS user_id, 
PLM_user.username AS username, 
RB_person.id AS person_id, 
RB_person.firstname AS firstname, 
RB_person.lastname AS lastname, 
PLM_userAccount.id AS account_id, 
PLM_userAccount.title AS title, 
PLM_userAccount.description AS description, 
PLM_userAccount.administrator AS administrator 
FROM PLM_user 
INNER JOIN RB_person ON RB_person.id = PLM_user.person_id 
INNER JOIN PLM_userToAccount ON PLM_userToAccount.user_id = PLM_user.id 
INNER JOIN PLM_userAccount ON PLM_userAccount.id = PLM_userToAccount.account_id 
WHERE PLM_user.username = '$username' AND PLM_user.password = '$password' AND administrator = 1