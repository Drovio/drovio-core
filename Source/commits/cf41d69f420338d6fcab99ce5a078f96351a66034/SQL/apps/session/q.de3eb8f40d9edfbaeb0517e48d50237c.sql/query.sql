-- Set application id
SELECT {app_id} INTO @applicationID;

-- Clean up sessions older than one week
DELETE FROM AC_app_session
WHERE application_id = @applicationID AND (UNIX_TIMESTAMP() - time_updated) > ({expire_days});

-- Get guest sessions
SELECT *
FROM AC_app_session
WHERE application_id = @applicationID
ORDER BY AC_app_session.time_updated DESC;