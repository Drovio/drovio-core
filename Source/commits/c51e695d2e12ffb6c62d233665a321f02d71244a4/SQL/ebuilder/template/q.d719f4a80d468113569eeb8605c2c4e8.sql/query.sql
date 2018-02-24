SELECT eBLD_siteTemplateGroup.id AS groupID,
       eBLD_siteTemplateGroupLiterals.title AS groupTitle
FROM eBLD_siteTemplateGroup
INNER JOIN eBLD_siteTemplateGroupLiterals
ON  eBLD_siteTemplateGroupLiterals.locale = '$locale'