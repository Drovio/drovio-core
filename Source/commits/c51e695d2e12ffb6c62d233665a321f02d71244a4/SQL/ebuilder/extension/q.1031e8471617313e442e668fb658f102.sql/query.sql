SELECT eBLD_extension.id AS extensionID,
       eBLD_extensionLiterals.title AS extensionTitle
FROM eBLD_extension
INNER JOIN eBLD_extensionLiterals
ON  eBLD_extensionLiterals.locale = '$locale'
WHERE eBLD_extension.owner  = '$userId'
AND eBLD_extension.status = '$status'