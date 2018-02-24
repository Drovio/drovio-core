SELECT eBLD_siteTemplate.id AS templateID,
       eBLD_siteTemplateLiterals.title AS templateTitle
FROM eBLD_siteTemplate
INNER JOIN eBLD_siteTemplateLiterals
ON  eBLD_siteTemplate.id = eBLD_siteTemplateLiterals.id
WHERE eBLD_siteTemplate.owner  = '$userId'
AND eBLD_siteTemplate.status = '$status'
AND eBLD_siteTemplateLiterals.locale = '$locale'