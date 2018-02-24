-- Get base and current version
SELECT base_version, current_version INTO @baseVersion, @currentVersion
FROM VCS_projectBranch
WHERE project_id = {pid} AND branch_name = '{branch}';

-- Get package version build 
SELECT build INTO @versionBuild
FROM VCS_projectReleasePackage
WHERE project_id = {pid} AND branch_name = '{branch}' AND version = @currentVersion;

-- Select all information 
SELECT @baseVersion as baseVersion, @currentVersion as currentVersion, @versionBuild as versionBuild;