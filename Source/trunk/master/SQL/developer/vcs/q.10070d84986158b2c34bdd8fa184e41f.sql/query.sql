-- Add a history release entry
INSERT INTO VCS_projectRelease (project_id, branch_name, version, build, time, title, description)
VALUES ({pid}, '{branch}', '{version}', {build}, {time}, '{title}', '{description}');

-- Insert or Update the release package with the information 
INSERT INTO VCS_projectReleasePackage (project_id, branch_name, version, build)
VALUES ({pid}, '{branch}', '{version}', {build}) 
ON DUPLICATE KEY UPDATE build = {build};

-- Update branch versions
UPDATE VCS_projectBranch
SET base_version = IFNULL(base_version, '{version}'), current_version = '{version}'
WHERE project_id = {pid};