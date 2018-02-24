SELECT RB_branch.*, RB_branchLocationData.* 
FROM RB_company 
INNER JOIN RB_branch ON RB_company.id = RB_branch.company_id 
LEFT OUTER JOIN RB_branchLocationData ON RB_branchLocationData.branch_id = RB_branch.id 
WHERE RB_company.id = $cid