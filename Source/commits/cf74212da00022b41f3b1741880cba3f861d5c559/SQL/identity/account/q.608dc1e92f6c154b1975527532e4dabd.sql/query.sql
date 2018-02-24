SELECT ID_account.*, ID_person.id AS person_id
FROM ID_account
INNER JOIN ID_personToAccount ON ID_account.id = ID_personToAccount.account_id
INNER JOIN ID_person ON ID_personToAccount.person_id = ID_person.id
WHERE (ID_person.mail = '{username}' OR ID_account.username = '{username}');