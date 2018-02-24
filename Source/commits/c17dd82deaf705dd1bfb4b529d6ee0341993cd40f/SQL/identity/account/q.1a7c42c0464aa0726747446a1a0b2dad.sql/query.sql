SELECT ID_person.*
FROM ID_person
LEFT OUTER JOIN ID_personToAccount ON ID_person.id = ID_personToAccount.person_id
WHERE ID_personToAccount.account_id = {aid};