-- Get next product id
SELECT IFNULL(MAX(branch_id), 0) + 1 INTO @branchID
FROM RTL_Cbranch
WHERE company_id = {cid};

-- Insert Company Product
INSERT INTO RTL_Cbranch (id, company_id, branch_id, title, address)
VALUES (CONCAT({cid}, '_', @branchID), {cid}, @branchID, '{title}', '{address}');

/* Get product id */
SELECT last_insert_id() AS id;