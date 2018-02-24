-- Get next product id
SELECT IFNULL(MAX(branch_id), 0) + 1 INTO @companyBranchID
FROM RTL_Cbranch
WHERE company_id = {cid};

-- Set branch id
SELECT CONCAT({cid}, '_', @companyBranchID) INTO @branchID;

-- Insert Company Product
INSERT INTO RTL_Cbranch (id, company_id, branch_id, title, address)
VALUES (@branchID, {cid}, @companyBranchID, '{title}', '{address}');

/* Get branch id */
SELECT @companyBranchID AS id;