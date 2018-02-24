SELECT RB_company.*, 
RB_branch.name AS branch_name, 
RB_branch.description AS branch_description, 
RB_branchLocationData.* 
FROM RB_company 
LEFT OUTER JOIN RB_branch ON RB_company.home_branch_id = RB_branch.id 
LEFT OUTER JOIN RB_branchLocationData ON RB_branchLocationData.branch_id = RB_branch.id 
WHERE RB_company.id = $cid