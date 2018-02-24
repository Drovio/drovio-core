UPDATE eBLD_siteTemplate 
SET type = '$type', templateGroup = '$templateGroup', date_modified =  NOW(), status = '$status'
WHERE id = '$id'