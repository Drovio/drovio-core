SELECT eBLD_siteTemplate.id AS templateID,
       eBLD_siteTemplateLiterals.title AS templateTitle
FROM eBLD_siteTemplate
INNER JOIN eBLD_siteTemplateLiterals
ON  eBLD_siteTemplateLiterals.locale = '$locale'
WHERE eBLD_siteTemplate.status = '$status'