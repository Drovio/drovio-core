SELECT ID_account.*, ID_person.id AS person_id, ID_person.mail, ID_person.firstname, ID_person.middle_name, ID_person.lastname
FROM ID_account
LEFT OUTER JOIN ID_personToAccount ON ID_account.id = ID_personToAccount.account_id
LEFT OUTER JOIN ID_person ON ID_person.id = ID_personToAccount.person_id
WHERE ID_account.username = '{username}' OR ({wmail} = 1 AND ID_person.mail = '{username}');