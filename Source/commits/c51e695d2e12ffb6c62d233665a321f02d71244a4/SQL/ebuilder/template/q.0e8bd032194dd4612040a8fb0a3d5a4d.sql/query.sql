SELECT eBLD_siteTemplate.id AS templateID,
	eBLD_siteTemplate.status AS templateStatus,
	eBLD_siteTemplate.templateGroup,
	eBLD_siteTemplate.type AS templateType
FROM eBLD_siteTemplate
WHERE eBLD_siteTemplate.id = '$id'