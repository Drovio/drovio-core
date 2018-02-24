UPDATE PLM_account
SET salt = '$salt' 
WHERE PLM_account.id = $id