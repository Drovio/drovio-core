SELECT ID_account.*
FROM ID_account
INNER JOIN ID_personToAccount ON ID_account.id = ID_personToAccount.account_id
INNER JOIN ID_person ON ID_person.id = ID_personToAccount.person_id
WHERE ID_account.administrator = 1 AND ID_person.id = {pid};