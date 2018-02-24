SELECT ID_account.*, ID_person.id AS person_id, ID_person.mail
FROM ID_account
INNER JOIN ID_personToAccount ON ID_account.id = ID_personToAccount.account_id
INNER JOIN ID_person ON ID_person.id = ID_personToAccount.person_id
GROUP BY ID_account.id;