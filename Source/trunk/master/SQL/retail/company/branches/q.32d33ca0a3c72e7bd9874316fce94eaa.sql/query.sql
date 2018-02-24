-- Get next branch id
SELECT IFNULL(MAX(branch_id), 0) + 1 INTO @companyBranchID
FROM RTL_companyBranch
WHERE company_id = {cid};

-- Set branch id
SELECT CONCAT({cid}, '_', @companyBranchID) INTO @branchID;

-- Create new company branch
INSERT INTO RTL_companyBranch (id, company_id, branch_id, title, address)
VALUES (@branchID, {cid}, @companyBranchID, '{title}', '{address}');

/* Get branch id */
SELECT @companyBranchID AS id;