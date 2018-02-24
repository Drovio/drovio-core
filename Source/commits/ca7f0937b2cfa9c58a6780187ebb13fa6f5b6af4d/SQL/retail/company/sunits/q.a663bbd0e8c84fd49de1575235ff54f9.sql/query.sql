-- Get next storage unit id
SELECT IFNULL(MAX(storage_id), 0) + 1 INTO @companyStorageID
FROM RTL_storageUnit
WHERE company_id = {cid};

-- Set storage unit id
SELECT CONCAT({cid}, '_', @companyStorageID) INTO @storageUnitID;

-- Create new company storage unit
INSERT INTO RTL_storageUnit (id, company_id, storage_id, title, description)
VALUES (@storageUnitID, {cid}, @companyStorageID, '{title}', '{description}');

/* Get storage unit id */
SELECT @storageUnitID AS id;