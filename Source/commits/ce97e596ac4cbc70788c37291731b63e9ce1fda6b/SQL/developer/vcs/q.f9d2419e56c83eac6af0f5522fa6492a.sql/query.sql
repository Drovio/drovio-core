-- Add to items working table (update time and force commit if already there)
INSERT INTO VCS_projectWorkingItem (project_id, branch_name, item_id, last_update, last_author_id, force_commit)
VALUES ({pid}, '{branch}', '{item}', {time}, {author}, {force_commit}) 
ON DUPLICATE KEY UPDATE last_update = {time}, last_author_id = {author}, force_commit = {force_commit};

-- Add to authors working table (if not there)
INSERT IGNORE INTO VCS_projectWorkingItemAuthor (project_id, item_id, author_id)
VALUES ({pid}, '{item}', {author});