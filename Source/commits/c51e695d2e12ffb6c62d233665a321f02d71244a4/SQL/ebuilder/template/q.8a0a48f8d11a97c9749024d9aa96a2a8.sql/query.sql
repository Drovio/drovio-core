SELECT eBLD_siteTemplate.id AS templateID,
	eBLD_siteType.type AS templateType,
	eBLD_siteTemplateLiterals.title AS templateTitle,
	eBLD_siteTemplateLiterals.description AS templateDescription,
	eBLD_siteTemplate.status AS templateStatus,
	groupID, groupTitle, groupDescription
FROM eBLD_siteTemplate
INNER JOIN  
      ( SELECT eBLD_siteTemplateGroup.id AS groupID,
                      eBLD_siteTemplateGroupLiterals.title AS groupTitle,
                      eBLD_siteTemplateGroupLiterals.description AS groupDescription
        FROM eBLD_siteTemplateGroup
        INNER JOIN eBLD_siteTemplateGroupLiterals
        ON eBLD_siteTemplateGroup.id = eBLD_siteTemplateGroupLiterals.id 
        WHERE eBLD_siteTemplateGroupLiterals.locale = '$locale'
      ) AS templateGroup
ON eBLD_siteTemplate.templateGroup = groupID
INNER JOIN eBLD_siteType
ON eBLD_siteTemplate.type = eBLD_siteType.id
INNER JOIN eBLD_siteTemplateLiterals
ON eBLD_siteTemplate.id = eBLD_siteTemplateLiterals.id 
AND eBLD_siteTemplateLiterals.locale = '$locale'
WHERE eBLD_siteTemplate.id = '$id'