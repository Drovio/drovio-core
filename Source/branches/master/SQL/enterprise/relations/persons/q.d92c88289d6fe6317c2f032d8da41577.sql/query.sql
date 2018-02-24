-- Get next relation id
SELECT IFNULL(MAX(relation_id), 0) + 1 INTO @teamRelationID
FROM RL_person
WHERE owner_team_id = {tid};

-- Set relation id
SELECT CONCAT({tid}, '_', @teamRelationID) INTO @relationID;

-- Insert team relation
INSERT INTO RL_person (id, owner_team_id, relation_id, firstname, middle_name, lastname)
VALUES (@relationID, {tid}, @teamRelationID, '{firstname}', '{middle_name}', '{lastname}');

-- Get relation id
SELECT @relationID AS id;