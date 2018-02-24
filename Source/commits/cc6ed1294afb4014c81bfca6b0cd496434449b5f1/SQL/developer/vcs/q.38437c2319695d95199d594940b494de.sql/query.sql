-- Get branches count
SELECT COUNT(*) INTO @branchesCount
FROM VCS_projectBranch
WHERE project_id = {pid};

-- Get releases count 
SELECT COUNT(*) INTO @releasesCount
FROM VCS_projectReleasePackage
WHERE project_id = {pid};

-- Get commits count 
SELECT COUNT(*) INTO @commitsCount
FROM VCS_projectCommit
WHERE project_id = {pid};

-- Return all information together 
SELECT @branchesCount as branchesCount, @releasesCount as releasesCount, @commitsCount as commitsCount;