-- Set application id
SELECT {app_id} INTO @applicationID;

-- Get guest sessions
SELECT COUNT(*) INTO @null_count
FROM AC_app_session
WHERE application_id = @applicationID AND account_id IS NULL;

-- Get registered user sessions
SELECT COUNT(*) INTO @count
FROM AC_app_session
WHERE application_id = @applicationID AND account_id IS NOT NULL;

-- Get both results
SELECT @null_count AS guests, @count AS users;