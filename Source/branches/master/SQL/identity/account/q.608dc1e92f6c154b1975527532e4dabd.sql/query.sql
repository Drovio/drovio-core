SELECT ID_account.*, ID_person.id AS person_id
FROM ID_account
LEFT OUTER JOIN ID_personToAccount ON ID_account.id = ID_personToAccount.account_id
LEFT OUTER JOIN ID_person ON ID_person.id = ID_personToAccount.person_id
WHERE (ID_person.mail = '{username}' OR ID_account.username = '{username}');