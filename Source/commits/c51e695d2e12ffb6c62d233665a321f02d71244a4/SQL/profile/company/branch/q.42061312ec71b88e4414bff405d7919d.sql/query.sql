-- Get the next branch id for the company
SET @branchID := (SELECT IFNULL(MAX(id),0)+1 FROM RB_branch WHERE company_id = $cid);

-- Insert the branch
INSERT INTO RB_branch (branch_id, company_id, name, description) 
VALUES (@branchID, $cid, '$name', '$description');