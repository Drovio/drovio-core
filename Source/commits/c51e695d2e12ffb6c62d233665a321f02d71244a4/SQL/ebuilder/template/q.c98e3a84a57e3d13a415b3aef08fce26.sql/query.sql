SELECT eBLD_siteTemplateGroup.id AS groupID,
       eBLD_siteTemplateGroupLiterals.title AS groupTitle,
       eBLD_siteTemplateGroupLiterals.description AS groupDescription
FROM eBLD_siteTemplateGroup
INNER JOIN eBLD_siteTemplateGroupLiterals
ON  eBLD_siteTemplateGroupLiterals.locale = '$locale'
WHERE eBLD_siteTemplateGroup.id = '$id'